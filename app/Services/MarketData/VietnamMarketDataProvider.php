<?php

namespace App\Services\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class VietnamMarketDataProvider
 *
 * Provider adapter for Vietnam stock market quotes (HOSE, HNX, UPCOM)
 * using public open-access endpoints (e.g. TCBS OpenAPI adapter).
 */
class VietnamMarketDataProvider implements MarketDataProviderInterface
{
    protected string $baseUrl;

    protected ?string $apiKey;

    protected int $timeout;

    /**
     * @var list<string>
     */
    protected array $supportedExchanges;

    public function __construct()
    {
        $config = config('market_data.providers.vietnam', []);

        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'https://apipubaws.tcbs.com.vn'), '/');
        $this->apiKey = ! empty($config['api_key']) ? (string) $config['api_key'] : null;
        $this->timeout = (int) ($config['timeout'] ?? 5);
        $this->supportedExchanges = $config['supported_exchanges'] ?? ['HOSE', 'HNX', 'UPCOM'];
    }

    public function getIdentifier(): string
    {
        return 'vietnam';
    }

    public function supportsExchange(?string $exchange): bool
    {
        if ($exchange === null) {
            return true;
        }

        return in_array(strtoupper(trim($exchange)), $this->supportedExchanges, true);
    }

    public function getQuote(string $symbol, ?string $exchange = null): ?MarketQuote
    {
        $cleanSymbol = strtoupper(trim($symbol));

        if (empty($cleanSymbol)) {
            return null;
        }

        try {
            $client = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'StockSystem-Portfolio/1.0',
                ]);

            if ($this->apiKey !== null) {
                $client = $client->withToken($this->apiKey);
            }

            $url = "{$this->baseUrl}/stock-insight/v1/stock/quotes";
            $response = $client->get($url, [
                'symbol' => $cleanSymbol,
            ]);

            if (! $response->successful()) {
                Log::warning('Vietnam market data quote request failed', [
                    'symbol' => $cleanSymbol,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $json = $response->json();

            return $this->normalizeResponse($cleanSymbol, $exchange, $json);
        } catch (Throwable $e) {
            Log::warning('Vietnam market data provider exception', [
                'symbol' => $cleanSymbol,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Normalize provider-specific JSON response into a standardized MarketQuote.
     */
    public function normalizeResponse(string $symbol, ?string $exchange, mixed $data): ?MarketQuote
    {
        if (empty($data)) {
            return null;
        }

        // Support array of records or single object
        $item = is_array($data) && isset($data[0]) ? $data[0] : (is_array($data) ? $data : null);

        if (! is_array($item)) {
            return null;
        }

        // Extract raw price
        $rawPrice = $item['price'] ?? $item['lastPrice'] ?? $item['matchPrice'] ?? $item['close'] ?? null;
        if ($rawPrice === null || ! is_numeric($rawPrice) || (float) $rawPrice <= 0) {
            return null;
        }

        $priceFloat = (float) $rawPrice;

        // In Vietnamese market conventions, some feeds quote in thousands (e.g. 32.45 = 32,450 VND).
        // If price is <= 1000, normalize to standard VND.
        if ($priceFloat <= 1000) {
            $priceFloat *= 1000;
        }

        $priceStr = (string) round($priceFloat, 2);

        // Previous close / Reference price
        $rawPrevClose = $item['previous_close'] ?? $item['refPrice'] ?? $item['cp'] ?? $item['prevClose'] ?? null;
        $prevCloseStr = null;
        if ($rawPrevClose !== null && is_numeric($rawPrevClose)) {
            $prevCloseFloat = (float) $rawPrevClose;
            if ($prevCloseFloat <= 1000 && $priceFloat > 1000) {
                $prevCloseFloat *= 1000;
            }
            $prevCloseStr = (string) round($prevCloseFloat, 2);
        }

        // Price change
        $rawChange = $item['change'] ?? $item['ch'] ?? $item['priceChange'] ?? null;
        $changeStr = null;
        if ($rawChange !== null && is_numeric($rawChange)) {
            $changeFloat = (float) $rawChange;
            if (abs($changeFloat) <= 10 && $priceFloat > 1000) {
                $changeFloat *= 1000;
            }
            $changeStr = (string) round($changeFloat, 2);
        } elseif ($prevCloseStr !== null) {
            $changeStr = (string) round($priceFloat - (float) $prevCloseStr, 2);
        }

        // Change percentage
        $rawPercent = $item['change_percent'] ?? $item['percentChange'] ?? $item['ra'] ?? $item['rate'] ?? null;
        $changePercentStr = null;
        if ($rawPercent !== null && is_numeric($rawPercent)) {
            $changePercentStr = (string) round((float) $rawPercent, 2);
        } elseif ($prevCloseStr !== null && (float) $prevCloseStr > 0 && $changeStr !== null) {
            $calcPercent = ((float) $changeStr / (float) $prevCloseStr) * 100;
            $changePercentStr = (string) round($calcPercent, 2);
        }

        // Volume
        $rawVolume = $item['volume'] ?? $item['totalVolume'] ?? $item['vo'] ?? null;
        $volumeStr = ($rawVolume !== null && is_numeric($rawVolume)) ? (string) round((float) $rawVolume) : null;

        // Company Name
        $name = $item['companyName'] ?? $item['name'] ?? null;

        // Exchange
        $resolvedExchange = $item['exchange'] ?? $item['floor'] ?? $exchange ?? 'HOSE';

        // Timestamp
        $timestamp = isset($item['timestamp']) ? Carbon::parse($item['timestamp']) : now();

        return new MarketQuote(
            symbol: $symbol,
            exchange: $resolvedExchange,
            price: $priceStr,
            name: $name,
            previousClose: $prevCloseStr,
            change: $changeStr,
            changePercent: $changePercentStr,
            volume: $volumeStr,
            timestamp: $timestamp,
            currency: 'VND'
        );
    }
}
