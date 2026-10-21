<?php

namespace App\Services\Financial;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ExchangeRateService
 *
 * Provides authoritative arbitrary-precision exchange rate lookups and currency conversions
 * without floating-point IEEE-754 inaccuracy.
 */
class ExchangeRateService
{
    /**
     * Get the exchange rate from $from to $to as an arbitrary-precision string.
     */
    public function getRate(string $from, string $to): ?string
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to) {
            return '1.00000000';
        }

        $pair = "{$from}_{$to}";
        $cacheTtl = (int) config('currency.rates.cache_ttl', 3600);

        try {
            return Cache::remember("fx_rate:{$pair}", $cacheTtl, function () use ($from, $to, $pair) {
                $rate = $this->resolveRate($from, $to, $pair);
                if ($rate !== null) {
                    Cache::put("fx_rate_ts:{$pair}", time(), $cacheTtl);
                }

                return $rate;
            });
        } catch (Throwable $e) {
            Log::warning('Exchange rate lookup cache error', [
                'pair' => $pair,
                'error' => $e->getMessage(),
            ]);

            return $this->resolveRate($from, $to, $pair);
        }
    }

    /**
     * Get rate metadata including source, estimate status, and timestamp.
     *
     * @return array{rate: ?string, from: string, to: string, source: string, is_estimate: bool, timestamp: int}
     */
    public function getRateDetails(string $from, string $to): array
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to) {
            return [
                'rate' => '1.00000000',
                'from' => $from,
                'to' => $to,
                'source' => 'identity',
                'is_estimate' => false,
                'timestamp' => time(),
            ];
        }

        $rate = $this->getRate($from, $to);
        $pair = "{$from}_{$to}";
        $timestamp = (int) Cache::get("fx_rate_ts:{$pair}", time());

        return [
            'rate' => $rate,
            'from' => $from,
            'to' => $to,
            'source' => (string) config('currency.rates.source', 'fixed_reference'),
            'is_estimate' => true,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * Convert a monetary amount from $from to $to using BCMath arbitrary precision.
     *
     * @return string|null Returns converted numeric string or null if exchange rate is unavailable.
     */
    public function convert(string|int|float $amount, string $from, string $to, ?int $scale = null): ?string
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        $normalized = FinancialMath::normalize($amount);

        if ($from === $to) {
            return $normalized;
        }

        $rate = $this->getRate($from, $to);
        if ($rate === null) {
            Log::warning('Exchange rate conversion failed: rate unavailable', [
                'from' => $from,
                'to' => $to,
                'amount' => $normalized,
            ]);

            return null;
        }

        $scale ??= FinancialMath::DEFAULT_SCALE;

        return FinancialMath::multiply($normalized, $rate, $scale);
    }

    /**
     * Resolve exchange rate from configured rate tables, inverse pairs, or external adapter.
     */
    protected function resolveRate(string $from, string $to, string $pair): ?string
    {
        $fixedRates = config('currency.rates.fixed', []);

        // 1. Direct configured pair lookup (e.g. TWD_USD => 0.03125)
        if (isset($fixedRates[$pair]) && is_numeric($fixedRates[$pair]) && (float) $fixedRates[$pair] > 0) {
            return FinancialMath::normalize($fixedRates[$pair]);
        }

        // 2. Inverse configured pair lookup (e.g. USD_TWD => 32.0 => 1 / 32 = 0.03125)
        $inversePair = "{$to}_{$from}";
        if (isset($fixedRates[$inversePair]) && is_numeric($fixedRates[$inversePair]) && (float) $fixedRates[$inversePair] > 0) {
            return FinancialMath::divide('1', $fixedRates[$inversePair], FinancialMath::DEFAULT_SCALE);
        }

        return null;
    }
}
