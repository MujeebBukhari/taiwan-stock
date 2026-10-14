<?php

namespace App\Services\MarketData;

use App\Models\Company;
use App\Services\Financial\FinancialMath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class MarketDataService
 *
 * Domain service providing real-time and cached market price data for companies.
 * Decouples controller and UI layers from external market data providers.
 */
class MarketDataService
{
    public function __construct(
        protected MarketDataProviderInterface $provider
    ) {}

    /**
     * Retrieve the current market quote specifically for a Company model.
     */
    public function getQuoteForCompany(Company $company): ?MarketQuote
    {
        return $this->getQuote($company);
    }

    /**
     * Retrieve the current market quote for a company or symbol with caching.
     */
    public function getQuote(Company|string $companyOrSymbol): ?MarketQuote
    {
        $symbol = strtoupper(trim(is_string($companyOrSymbol) ? $companyOrSymbol : $companyOrSymbol->symbol));
        $exchange = $companyOrSymbol instanceof Company ? $companyOrSymbol->exchange : null;

        if (empty($symbol)) {
            return null;
        }

        $cacheKey = "market_quote:{$symbol}";
        $fallbackKey = "market_quote_fallback:{$symbol}";
        $cacheTtl = (int) config('market_data.cache_ttl', 60);
        $fallbackTtl = (int) config('market_data.fallback_cache_ttl', 86400);

        // 1. Check active in-memory / cache store
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            $quote = is_array($cachedData) ? MarketQuote::fromArray($cachedData) : $cachedData;
            if ($quote instanceof MarketQuote && $companyOrSymbol instanceof Company && ! empty($companyOrSymbol->currency)) {
                $quote = $quote->withCurrency($companyOrSymbol->currency);
            }

            return $quote;
        }

        // 2. Fetch fresh quote from external provider
        try {
            $quote = $this->provider->getQuote($symbol, $exchange);

            if ($quote !== null) {
                if ($companyOrSymbol instanceof Company && ! empty($companyOrSymbol->currency)) {
                    $quote = $quote->withCurrency($companyOrSymbol->currency);
                }

                // Store in active cache
                Cache::put($cacheKey, $quote->toArray(), $cacheTtl);
                // Store in fallback cache for graceful degradation during future outages
                Cache::put($fallbackKey, $quote->toArray(), $fallbackTtl);

                return $quote;
            }
        } catch (Throwable $e) {
            Log::warning("MarketDataService error fetching quote for [{$symbol}]", [
                'error' => $e->getMessage(),
            ]);
        }

        // 3. Graceful degradation: Check fallback cache if provider is down or timed out
        $fallbackData = Cache::get($fallbackKey);
        if ($fallbackData !== null) {
            Log::info("Serving fallback quote for [{$symbol}] due to provider unavailability");

            $quote = is_array($fallbackData) ? MarketQuote::fromArray($fallbackData) : $fallbackData;
            if ($quote instanceof MarketQuote && $companyOrSymbol instanceof Company && ! empty($companyOrSymbol->currency)) {
                $quote = $quote->withCurrency($companyOrSymbol->currency);
            }

            return $quote;
        }

        // 4. Fallback to company's last entered transaction price if available
        if ($companyOrSymbol instanceof Company && ! empty($companyOrSymbol->last_price)) {
            return new MarketQuote(
                symbol: $companyOrSymbol->symbol,
                exchange: $companyOrSymbol->exchange,
                price: (string) $companyOrSymbol->last_price,
                name: $companyOrSymbol->name,
                currency: $companyOrSymbol->currency ?? 'TWD',
                timestamp: $companyOrSymbol->updated_at
            );
        }

        // 5. No quote available (do not return fake or zero price)
        return null;
    }

    /**
     * Retrieve quotes for a collection or array of companies/symbols.
     *
     * @param  iterable<Company|string>  $items
     * @return array<string, ?MarketQuote>
     */
    public function getQuotes(iterable $items): array
    {
        $quotes = [];

        foreach ($items as $item) {
            $symbol = strtoupper(trim($item instanceof Company ? $item->symbol : (string) $item));
            if (! empty($symbol)) {
                $quotes[$symbol] = $this->getQuote($item);
            }
        }

        return $quotes;
    }

    /**
     * Clear cached quote for a symbol.
     */
    public function clearCache(string $symbol): void
    {
        $cleanSymbol = strtoupper(trim($symbol));
        Cache::forget("market_quote:{$cleanSymbol}");
    }

    /**
     * Calculate Market Value: Current Price × Current Quantity.
     * Uses BCMath arbitrary precision.
     */
    public function calculateMarketValue(string|int|float $quantity, string|int|float $marketPrice): string
    {
        $normQty = FinancialMath::normalize($quantity);
        $normPrice = FinancialMath::normalize($marketPrice);

        return FinancialMath::multiply($normQty, $normPrice, FinancialMath::CURRENCY_SCALE);
    }

    /**
     * Calculate Unrealized Profit: Market Value − Current Inventory Cost.
     * Uses BCMath arbitrary precision.
     */
    public function calculateUnrealizedProfit(
        string|int|float $quantity,
        string|int|float $marketPrice,
        string|int|float $inventoryCost
    ): string {
        $marketValue = $this->calculateMarketValue($quantity, $marketPrice);
        $normCost = FinancialMath::normalize($inventoryCost);

        return FinancialMath::subtract($marketValue, $normCost, FinancialMath::CURRENCY_SCALE);
    }

    /**
     * Calculate Unrealized Profit Percentage: (Unrealized Profit ÷ Inventory Cost) × 100.
     */
    public function calculateUnrealizedProfitPercent(
        string|int|float $unrealizedProfit,
        string|int|float $inventoryCost
    ): string {
        $normCost = FinancialMath::normalize($inventoryCost);
        $normProfit = FinancialMath::normalize($unrealizedProfit);

        if (FinancialMath::isZero($normCost) || FinancialMath::isNegative($normCost)) {
            return '0.00';
        }

        $ratio = FinancialMath::divide($normProfit, $normCost, 6);

        return FinancialMath::multiply($ratio, '100', 2);
    }
}
