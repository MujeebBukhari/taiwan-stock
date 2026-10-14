<?php

namespace App\Services\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class TaiwanMarketDataProvider
 *
 * Provider adapter for Taiwan stock market quotes (TWSE - Taiwan Stock Exchange, TPEx - Taipei Exchange).
 */
class TaiwanMarketDataProvider implements MarketDataProviderInterface
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
        $config = config('market_data.providers.taiwan', []);

        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'https://mis.twse.com.tw'), '/');
        $this->apiKey = ! empty($config['api_key']) ? (string) $config['api_key'] : null;
        $this->timeout = (int) ($config['timeout'] ?? 5);
        $this->supportedExchanges = $config['supported_exchanges'] ?? ['TWSE', 'TPEX'];
    }

    public function getIdentifier(): string
    {
        return 'taiwan';
    }

    public function supportsExchange(?string $exchange): bool
    {
        if ($exchange === null) {
            return true;
        }

        return in_array(strtoupper(trim($exchange)), array_map('strtoupper', $this->supportedExchanges), true);
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
                    'User-Agent' => 'StockSystem-Taiwan/1.0',
                ]);

            if ($this->apiKey !== null) {
                $client = $client->withToken($this->apiKey);
            }

            // TWSE real-time / open quotes endpoint adapter
            $url = "{$this->baseUrl}/stock/api/getStockInfo.jsp";
            $resolvedEx = strtoupper(trim((string) ($exchange ?? 'TWSE')));
            $channelPrefix = in_array($resolvedEx, ['TPEX', 'OTC', 'ROCO'], true) ? 'otc' : 'tse';

            $response = $client->get($url, [
                'ex_ch' => "{$channelPrefix}_{$cleanSymbol}.tw",
                'json' => '1',
                'delay' => '0',
            ]);

            if (! $response->successful()) {
                Log::warning('Taiwan market data quote request failed', [
                    'symbol' => $cleanSymbol,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $json = $response->json();

            return $this->normalizeResponse($cleanSymbol, $exchange, $json);
        } catch (Throwable $e) {
            Log::warning('Taiwan market data provider exception', [
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

        // Support msgArray structure from TWSE MIS format or generic array
        $item = null;
        if (is_array($data)) {
            if (isset($data['msgArray']) && is_array($data['msgArray']) && isset($data['msgArray'][0])) {
                $item = $data['msgArray'][0];
            } elseif (isset($data[0]) && is_array($data[0])) {
                $item = $data[0];
            } else {
                $item = $data;
            }
        }

        if (! is_array($item)) {
            return null;
        }

        // Extract raw price: 'z' in TWSE format is last trade price, or 'price'/'close'
        $rawPrice = $item['z'] ?? $item['price'] ?? $item['lastPrice'] ?? $item['matchPrice'] ?? $item['close'] ?? null;
        if ($rawPrice === null || $rawPrice === '-' || ! is_numeric($rawPrice) || (float) $rawPrice <= 0) {
            return null;
        }

        $priceFloat = (float) $rawPrice;
        $priceStr = (string) round($priceFloat, 2);

        // Previous close / Reference price ('y' in TWSE format)
        $rawPrevClose = $item['y'] ?? $item['previous_close'] ?? $item['prevClose'] ?? $item['refPrice'] ?? null;
        $prevCloseStr = null;
        if ($rawPrevClose !== null && $rawPrevClose !== '-' && is_numeric($rawPrevClose)) {
            $prevCloseStr = (string) round((float) $rawPrevClose, 2);
        }

        // Price change
        $rawChange = $item['change'] ?? $item['priceChange'] ?? null;
        $changeStr = null;
        if ($rawChange !== null && $rawChange !== '-' && is_numeric($rawChange)) {
            $changeStr = (string) round((float) $rawChange, 2);
        } elseif ($prevCloseStr !== null) {
            $changeStr = (string) round($priceFloat - (float) $prevCloseStr, 2);
        }

        // Change percentage
        $rawPercent = $item['change_percent'] ?? $item['percentChange'] ?? null;
        $changePercentStr = null;
        if ($rawPercent !== null && $rawPercent !== '-' && is_numeric($rawPercent)) {
            $changePercentStr = (string) round((float) $rawPercent, 2);
        } elseif ($prevCloseStr !== null && (float) $prevCloseStr > 0 && $changeStr !== null) {
            $calcPercent = ((float) $changeStr / (float) $prevCloseStr) * 100;
            $changePercentStr = (string) round($calcPercent, 2);
        }

        // Volume ('v' in TWSE is total accumulated volume)
        $rawVolume = $item['v'] ?? $item['volume'] ?? $item['totalVolume'] ?? null;
        $volumeStr = ($rawVolume !== null && $rawVolume !== '-' && is_numeric($rawVolume)) ? (string) round((float) $rawVolume) : null;

        // Company Name ('n' or 'nf' in TWSE format)
        $name = $item['n'] ?? $item['nf'] ?? $item['companyName'] ?? $item['name'] ?? null;

        // Exchange
        $resolvedExchange = $exchange ?? (isset($item['ex']) && $item['ex'] === 'otc' ? 'TPEx' : 'TWSE');

        // Timestamp
        $timestamp = isset($item['t']) && isset($item['d'])
            ? Carbon::parse($item['d'].' '.$item['t'])
            : (isset($item['timestamp']) ? Carbon::parse($item['timestamp']) : now());

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
            currency: 'TWD'
        );
    }
}
