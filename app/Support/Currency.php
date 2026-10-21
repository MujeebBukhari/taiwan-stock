<?php

namespace App\Support;

use App\Models\User;
use App\Services\Financial\ExchangeRateService;
use App\Services\Financial\FinancialMath;
use Illuminate\Support\Facades\Schema;

class Currency
{
    public const TWD = 'TWD';

    public const USD = 'USD';

    public const SUPPORTED_CURRENCIES = [self::TWD, self::USD];

    /**
     * Get the primary default currency code.
     */
    public static function default(): string
    {
        return config('currency.default', self::TWD);
    }

    /**
     * Alias for default().
     */
    public static function getDefault(): string
    {
        return self::default();
    }

    /**
     * Get all supported currency codes.
     *
     * @return list<string>
     */
    public static function getSupportedCodes(): array
    {
        return array_keys(config('currency.currencies', [
            self::TWD => [],
            self::USD => [],
        ]));
    }

    /**
     * Get the secondary currency code.
     */
    public static function secondary(): string
    {
        return config('currency.secondary', self::USD);
    }

    /**
     * Determine if a given currency code is among configured supported currencies.
     */
    public static function isSupported(?string $currency): bool
    {
        if (empty($currency)) {
            return false;
        }

        $code = strtoupper(trim($currency));

        return array_key_exists($code, config('currency.currencies', []));
    }

    /**
     * Get the currently active user display currency.
     * Hierarchy:
     * 1. Authenticated user's persisted preference
     * 2. Session preference
     * 3. System default (TWD)
     */
    public static function getUserCurrency(): string
    {
        if (auth()->check()) {
            $userCurrency = auth()->user()->currency;
            if (! empty($userCurrency) && self::isSupported($userCurrency)) {
                return strtoupper(trim($userCurrency));
            }
        }

        if (session()->has('currency')) {
            $sessionCurrency = (string) session('currency');
            if (self::isSupported($sessionCurrency)) {
                return strtoupper(trim($sessionCurrency));
            }
        }

        return self::default();
    }

    /**
     * Persist display currency selection to session and to user record (if authenticated).
     */
    public static function setUserCurrency(string $currency, ?User $user = null): bool
    {
        $code = strtoupper(trim($currency));
        if (! self::isSupported($code)) {
            return false;
        }

        session(['currency' => $code]);

        $targetUser = $user ?? auth()->user();
        if ($targetUser && Schema::hasColumn('users', 'currency')) {
            $targetUser->update(['currency' => $code]);
        }

        return true;
    }

    /**
     * Get currency display symbol.
     */
    public static function symbol(?string $currency = null): string
    {
        $code = strtoupper(trim((string) ($currency ?: self::getUserCurrency())));

        return match ($code) {
            'TWD', 'NTD' => 'NT$',
            'USD' => 'US$',
            'PKR' => 'Rs',
            'EUR' => '€',
            'GBP' => '£',
            default => $code,
        };
    }

    /**
     * Alias for symbol().
     */
    public static function getSymbol(?string $currency = null): string
    {
        return self::symbol($currency);
    }

    /**
     * Get currency official full name.
     */
    public static function name(?string $currency = null): string
    {
        $code = strtoupper(trim((string) ($currency ?: self::getUserCurrency())));

        return match ($code) {
            'TWD', 'NTD' => 'New Taiwan Dollar',
            'USD' => 'US Dollar',
            'PKR' => 'Pakistani Rupee',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            default => $code,
        };
    }

    /**
     * Get current exchange rate between two currencies.
     */
    public static function getRate(string $from, string $to): ?string
    {
        return app(ExchangeRateService::class)->getRate($from, $to);
    }

    /**
     * Get rate metadata including source, estimate indicator, and timestamp.
     *
     * @return array{rate: ?string, from: string, to: string, source: string, is_estimate: bool, timestamp: int}
     */
    public static function getRateDetails(?string $from = null, ?string $to = null): array
    {
        $fromCode = $from ?: self::TWD;
        $toCode = $to ?: self::getUserCurrency();

        return app(ExchangeRateService::class)->getRateDetails($fromCode, $toCode);
    }

    /**
     * Convert an arbitrary precision amount from one currency to another.
     */
    public static function convert(string|int|float $amount, string $from, string $to): ?string
    {
        return app(ExchangeRateService::class)->convert($amount, $from, $to);
    }

    /**
     * Convert amount to target currency (defaulting to active display currency) and return raw numeric string.
     */
    public static function convertAmount(string|int|float $amount, string $from = self::TWD, ?string $to = null): string
    {
        $fromCode = strtoupper(trim($from));
        $toCode = strtoupper(trim((string) ($to ?: self::getUserCurrency())));

        if ($fromCode === $toCode) {
            return FinancialMath::normalize($amount);
        }

        $converted = self::convert($amount, $fromCode, $toCode);

        return $converted ?? FinancialMath::normalize($amount);
    }

    /**
     * Format a monetary amount with currency symbol using arbitrary precision rounding.
     * Places the negative sign before the currency symbol (e.g. -NT$1,250, -US$25.50).
     */
    public static function format(string|int|float $amount, ?string $currency = null, ?int $decimals = null, bool $showPlusSign = false): string
    {
        $code = strtoupper(trim((string) ($currency ?: self::getUserCurrency())));
        $symbol = self::symbol($code);

        $dec = $decimals ?? (in_array($code, [self::TWD, 'NTD'], true) ? 0 : 2);

        $normalized = FinancialMath::normalize($amount);
        $isNegative = FinancialMath::compare($normalized, '0') < 0;
        $isPositive = FinancialMath::compare($normalized, '0') > 0;

        $absAmount = $isNegative ? FinancialMath::multiply($normalized, '-1') : $normalized;
        $formattedNumber = FinancialMath::format($absAmount, $dec);

        if ($isNegative) {
            return '-'.$symbol.$formattedNumber;
        }

        if ($showPlusSign && $isPositive) {
            return '+'.$symbol.$formattedNumber;
        }

        return $symbol.$formattedNumber;
    }

    /**
     * Convert from source currency to target display currency and format cleanly.
     * If rate conversion is unavailable, gracefully falls back to formatting in source currency.
     */
    public static function formatConverted(string|int|float $amount, string $from = self::TWD, ?string $to = null, ?int $decimals = null, bool $showPlusSign = false): string
    {
        $fromCode = strtoupper(trim($from));
        $toCode = strtoupper(trim((string) ($to ?: self::getUserCurrency())));

        if ($fromCode === $toCode) {
            return self::format($amount, $toCode, $decimals, $showPlusSign);
        }

        $converted = self::convert($amount, $fromCode, $toCode);
        if ($converted !== null) {
            return self::format($converted, $toCode, $decimals, $showPlusSign);
        }

        // Graceful fallback to original currency if rate is unavailable
        return self::format($amount, $fromCode, $decimals, $showPlusSign);
    }
}
