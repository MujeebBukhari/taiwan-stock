<?php

namespace Tests\Unit\Financial;

use App\Services\Financial\FinancialMath;
use DivisionByZeroError;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FinancialMathTest extends TestCase
{
    public function test_precise_addition(): void
    {
        // Explicit requirement: 10.50 + 2.25 = 12.75
        $sum = FinancialMath::add('10.50', '2.25', FinancialMath::CURRENCY_SCALE);
        $this->assertSame('12.75', $sum);

        // High precision addition with default scale (8)
        $preciseSum = FinancialMath::add('100.12345678', '200.87654321');
        $this->assertSame('300.99999999', $preciseSum);
    }

    public function test_precise_subtraction(): void
    {
        $diff = FinancialMath::subtract('100.00', '45.35', FinancialMath::CURRENCY_SCALE);
        $this->assertSame('54.65', $diff);

        $negativeDiff = FinancialMath::subtract('10.00', '25.50', FinancialMath::CURRENCY_SCALE);
        $this->assertSame('-15.50', $negativeDiff);
    }

    public function test_precise_multiplication(): void
    {
        // Explicit requirement: 100 * 53.333333
        $productScale4 = FinancialMath::multiply('100', '53.333333', FinancialMath::PRICE_SCALE);
        $this->assertSame('5333.3333', $productScale4);

        $productDefaultScale = FinancialMath::multiply('100', '53.333333', FinancialMath::DEFAULT_SCALE);
        $this->assertSame('5333.33330000', $productDefaultScale);
    }

    public function test_precise_division(): void
    {
        // Explicit requirement: 100 / 3
        $divScale8 = FinancialMath::divide('100', '3', FinancialMath::DEFAULT_SCALE);
        $this->assertSame('33.33333333', $divScale8);

        $divScale4 = FinancialMath::divide('100', '3', FinancialMath::PRICE_SCALE);
        $this->assertSame('33.3333', $divScale4);

        $divScale2 = FinancialMath::divide('100', '3', FinancialMath::CURRENCY_SCALE);
        $this->assertSame('33.33', $divScale2);
    }

    public function test_division_by_zero_throws_exception(): void
    {
        $this->expectException(DivisionByZeroError::class);
        FinancialMath::divide('500', '0');
    }

    public function test_division_by_float_zero_throws_exception(): void
    {
        $this->expectException(DivisionByZeroError::class);
        FinancialMath::divide('500', '0.00000000');
    }

    public function test_decimal_comparison(): void
    {
        $this->assertSame(0, FinancialMath::compare('10.50', '10.5000', FinancialMath::CURRENCY_SCALE));
        $this->assertSame(1, FinancialMath::compare('10.51', '10.50', FinancialMath::CURRENCY_SCALE));
        $this->assertSame(-1, FinancialMath::compare('10.49', '10.50', FinancialMath::CURRENCY_SCALE));

        $this->assertTrue(FinancialMath::equals('250.00', '250.0000', FinancialMath::CURRENCY_SCALE));
        $this->assertTrue(FinancialMath::greaterThan('100.01', '100.00'));
        $this->assertTrue(FinancialMath::greaterThanOrEqual('100.00', '100.00'));
        $this->assertTrue(FinancialMath::lessThan('99.99', '100.00'));
        $this->assertTrue(FinancialMath::lessThanOrEqual('100.00', '100.00'));

        $this->assertTrue(FinancialMath::isZero('0.00000000'));
        $this->assertTrue(FinancialMath::isPositive('0.0001'));
        $this->assertTrue(FinancialMath::isNegative('-0.0001'));
    }

    public function test_half_up_rounding(): void
    {
        // Standard rounding: 12.345 rounds to 12.35 at scale 2
        $this->assertSame('12.35', FinancialMath::round('12.345', 2));

        // 12.344 rounds to 12.34 at scale 2
        $this->assertSame('12.34', FinancialMath::round('12.344', 2));

        // 4 decimal places rounding for prices/WAC
        $this->assertSame('53.3333', FinancialMath::round('53.333333', 4));
        $this->assertSame('53.3334', FinancialMath::round('53.333366', 4));

        // Rounding negative numbers
        $this->assertSame('-12.35', FinancialMath::round('-12.345', 2));
        $this->assertSame('-12.34', FinancialMath::round('-12.344', 2));

        // Integer numbers
        $this->assertSame('100.00', FinancialMath::round('100', 2));
    }

    public function test_formatting(): void
    {
        $this->assertSame('1,234,567.89', FinancialMath::format('1234567.891'));
        $this->assertSame('50.00', FinancialMath::format('50'));
    }

    public function test_invalid_input_handling(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FinancialMath::normalize('not-a-number');
    }
}
