<?php

namespace App\Services\Financial;

use DivisionByZeroError;
use InvalidArgumentException;

/**
 * Class FinancialMath
 *
 * Core Arbitrary-Precision Financial Math Service.
 *
 * Designed to be completely headless, portable, and decoupled from HTTP/Desktop layers.
 * Powered by PHP's BCMath extension to prevent IEEE-754 floating-point drift.
 */
class FinancialMath
{
    /**
     * Precision Policy Scales:
     * - DEFAULT_SCALE: 8 decimals for intermediate calculations (zero intermediate loss).
     * - CURRENCY_SCALE: 2 decimals for monetary sums, net profit, inventory valuation.
     * - PRICE_SCALE: 4 decimals for stock share buy/sell unit prices.
     * - QUANTITY_SCALE: 4 decimals for stock lot quantities and fractional shares.
     * - WAC_SCALE: 4 decimals for Weighted Average Cost per share.
     */
    public const DEFAULT_SCALE = 8;

    public const CURRENCY_SCALE = 2;

    public const PRICE_SCALE = 4;

    public const QUANTITY_SCALE = 4;

    public const WAC_SCALE = 4;

    /**
     * Add two arbitrary precision numbers.
     */
    public static function add(string|int|float $a, string|int|float $b, ?int $scale = null): string
    {
        $scale ??= self::DEFAULT_SCALE;

        return bcadd(self::normalize($a), self::normalize($b), $scale);
    }

    /**
     * Subtract $b from $a.
     */
    public static function subtract(string|int|float $a, string|int|float $b, ?int $scale = null): string
    {
        $scale ??= self::DEFAULT_SCALE;

        return bcsub(self::normalize($a), self::normalize($b), $scale);
    }

    /**
     * Multiply two arbitrary precision numbers.
     */
    public static function multiply(string|int|float $a, string|int|float $b, ?int $scale = null): string
    {
        $scale ??= self::DEFAULT_SCALE;

        return bcmul(self::normalize($a), self::normalize($b), $scale);
    }

    /**
     * Divide $a by $b.
     *
     * @throws DivisionByZeroError
     */
    public static function divide(string|int|float $a, string|int|float $b, ?int $scale = null): string
    {
        $scale ??= self::DEFAULT_SCALE;
        $normA = self::normalize($a);
        $normB = self::normalize($b);

        if (self::isZero($normB)) {
            throw new DivisionByZeroError('Division by zero encountered in FinancialMath calculation.');
        }

        return bcdiv($normA, $normB, $scale);
    }

    /**
     * Compare $a and $b.
     * Returns:
     *  -1 if $a < $b
     *   0 if $a == $b
     *   1 if $a > $b
     */
    public static function compare(string|int|float $a, string|int|float $b, ?int $scale = null): int
    {
        $scale ??= self::DEFAULT_SCALE;

        return bccomp(self::normalize($a), self::normalize($b), $scale);
    }

    /**
     * Determine whether $a equals $b.
     */
    public static function equals(string|int|float $a, string|int|float $b, ?int $scale = null): bool
    {
        return self::compare($a, $b, $scale) === 0;
    }

    /**
     * Determine whether $a is greater than $b.
     */
    public static function greaterThan(string|int|float $a, string|int|float $b, ?int $scale = null): bool
    {
        return self::compare($a, $b, $scale) === 1;
    }

    /**
     * Determine whether $a is greater than or equal to $b.
     */
    public static function greaterThanOrEqual(string|int|float $a, string|int|float $b, ?int $scale = null): bool
    {
        return self::compare($a, $b, $scale) >= 0;
    }

    /**
     * Determine whether $a is less than $b.
     */
    public static function lessThan(string|int|float $a, string|int|float $b, ?int $scale = null): bool
    {
        return self::compare($a, $b, $scale) === -1;
    }

    /**
     * Determine whether $a is less than or equal to $b.
     */
    public static function lessThanOrEqual(string|int|float $a, string|int|float $b, ?int $scale = null): bool
    {
        return self::compare($a, $b, $scale) <= 0;
    }

    /**
     * Determine whether $a is zero.
     */
    public static function isZero(string|int|float $a, ?int $scale = null): bool
    {
        return self::compare($a, '0', $scale) === 0;
    }

    /**
     * Determine whether $a is positive (> 0).
     */
    public static function isPositive(string|int|float $a, ?int $scale = null): bool
    {
        return self::compare($a, '0', $scale) === 1;
    }

    /**
     * Determine whether $a is negative (< 0).
     */
    public static function isNegative(string|int|float $a, ?int $scale = null): bool
    {
        return self::compare($a, '0', $scale) === -1;
    }

    /**
     * Absolute value of an arbitrary precision number.
     */
    public static function abs(string|int|float $a): string
    {
        $norm = self::normalize($a);

        return str_starts_with($norm, '-') ? substr($norm, 1) : $norm;
    }

    /**
     * Perform mathematically sound Half-Up Rounding (Round half away from zero).
     */
    public static function round(string|int|float $number, int $scale = 2): string
    {
        if ($scale < 0) {
            throw new InvalidArgumentException('Scale cannot be negative.');
        }

        $norm = self::normalize($number);

        // If the number doesn't have decimals or has fewer decimals than scale, format directly
        if (! str_contains($norm, '.')) {
            return $scale === 0 ? $norm : $norm.'.'.str_repeat('0', $scale);
        }

        $offset = '0.'.str_repeat('0', $scale).'5';

        if (self::compare($norm, '0', $scale + 1) >= 0) {
            $augmented = bcadd($norm, $offset, $scale + 1);

            return bcdiv($augmented, '1', $scale);
        }

        $augmented = bcsub($norm, $offset, $scale + 1);

        return bcdiv($augmented, '1', $scale);
    }

    /**
     * Format a number for financial display (e.g. 12345.67 => 12,345.67).
     */
    public static function format(
        string|int|float $number,
        int $decimals = 2,
        string $decimalSeparator = '.',
        string $thousandsSeparator = ','
    ): string {
        $rounded = self::round($number, $decimals);
        $parts = explode('.', $rounded);
        $intPart = number_format((float) $parts[0], 0, '', $thousandsSeparator);

        if ($decimals === 0) {
            return $intPart;
        }

        $decPart = isset($parts[1]) ? str_pad($parts[1], $decimals, '0', STR_PAD_RIGHT) : str_repeat('0', $decimals);

        return $intPart.$decimalSeparator.$decPart;
    }

    /**
     * Normalize input into a clean string for BCMath.
     *
     * @throws InvalidArgumentException
     */
    public static function normalize(string|int|float $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                throw new InvalidArgumentException('Float value cannot be NaN or Infinite.');
            }
            $string = sprintf('%.10F', $value);

            return rtrim(rtrim($string, '0'), '.');
        }

        $trimmed = trim($value);
        if ($trimmed === '' || ! is_numeric($trimmed)) {
            throw new InvalidArgumentException("Value [{$value}] is not a valid numeric string.");
        }

        // Convert potential scientific notation string (e.g., "1.2e-4") into standard decimal representation
        if (stripos($trimmed, 'e') !== false) {
            $floatVal = (float) $trimmed;
            $string = sprintf('%.10F', $floatVal);

            return rtrim(rtrim($string, '0'), '.');
        }

        return $trimmed;
    }
}
