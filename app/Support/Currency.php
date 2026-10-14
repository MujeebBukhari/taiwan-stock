<?php

namespace App\Support;

use App\Services\Financial\FinancialMath;

class Currency
{
    public const TWD = 'TWD';

    public const USD = 'USD';

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
     */
    public static function getSupportedCodes(): array
    {
        return array_keys(config('currency.currencies', [
            self::TWD => [],
            self::USD => [],
        ]));
    }

    /**
     * Alias for symbol().
     */
    public static function getSymbol(?string $currency = null): string
    {
        return self::symbol($currency);
    }

    /**
     * Get the secondary currency code.
     */
    public static function secondary(): string
    {
        return config('currency.secondary', self::USD);
    }

    /**
     * Get currency display symbol.
     */
    public static function symbol(?string $currency = null): string
    {
        $code = strtoupper(trim((string) ($currency ?: self::default())));

        return match ($code) {
            'TWD', 'NTD' => 'NT$',
            'USD' => 'US$',
            'VND' => '₫',
            'PKR' => 'Rs',
            'EUR' => '€',
            'GBP' => '£',
            default => $code,
        };
    }

    /**
     * Get currency official full name.
     */
    public static function name(?string $currency = null): string
    {
        $code = strtoupper(trim((string) ($currency ?: self::default())));

        return match ($code) {
            'TWD', 'NTD' => 'New Taiwan Dollar',
            'USD' => 'US Dollar',
            'VND' => 'Vietnamese Dong',
            'PKR' => 'Pakistani Rupee',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            default => $code,
        };
    }

    /**
     * Determine if a given currency code is among configured supported currencies.
     */
    public static function isSupported(string $currency): bool
    {
        $code = strtoupper(trim($currency));
        $supported = config('currency.currencies', []);

        return array_key_exists($code, $supported);
    }

    /**
     * Format a monetary amount with currency symbol using arbitrary precision rounding.
     */
    public static function format(string|int|float $amount, ?string $currency = null, ?int $decimals = null): string
    {
        $code = strtoupper(trim((string) ($currency ?: self::default())));
        $symbol = self::symbol($code);

        $dec = $decimals ?? (in_array($code, [self::TWD, 'NTD', 'VND'], true) ? 0 : 2);

        $normalized = FinancialMath::normalize($amount);
        $isNegative = FinancialMath::compare($normalized, '0') < 0;

        $absAmount = $isNegative ? FinancialMath::multiply($normalized, '-1') : $normalized;
        $formattedNumber = FinancialMath::format($absAmount, $dec);

        return $isNegative ? '-'.$symbol.$formattedNumber : $symbol.$formattedNumber;
    }
}
