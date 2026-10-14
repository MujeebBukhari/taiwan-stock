<?php

namespace App\Services\MarketData;

use App\Support\Currency;
use Carbon\Carbon;

/**
 * Class MarketQuote
 *
 * Immutable Value Object representing a normalized stock market quote.
 */
class MarketQuote
{
    public function __construct(
        public readonly string $symbol,
        public readonly ?string $exchange,
        public readonly string $price,
        public readonly ?string $name = null,
        public readonly ?string $previousClose = null,
        public readonly ?string $change = null,
        public readonly ?string $changePercent = null,
        public readonly ?string $volume = null,
        public readonly ?Carbon $timestamp = null,
        public readonly string $currency = 'TWD'
    ) {}

    /**
     * Create a clone of this quote with an overridden currency.
     */
    public function withCurrency(string $currency): self
    {
        return new self(
            symbol: $this->symbol,
            exchange: $this->exchange,
            price: $this->price,
            name: $this->name,
            previousClose: $this->previousClose,
            change: $this->change,
            changePercent: $this->changePercent,
            volume: $this->volume,
            timestamp: $this->timestamp,
            currency: $currency
        );
    }

    /**
     * Determine if price change is strictly positive (> 0).
     */
    public function isPositiveChange(): bool
    {
        return $this->change !== null && (float) $this->change > 0;
    }

    /**
     * Determine if price change is strictly negative (< 0).
     */
    public function isNegativeChange(): bool
    {
        return $this->change !== null && (float) $this->change < 0;
    }

    /**
     * Formatted price string with currency symbol.
     */
    public function formattedPrice(): string
    {
        return Currency::format($this->price, $this->currency);
    }

    /**
     * Formatted price change with signed currency symbol (e.g. +NT$1,500 or -NT$500).
     */
    public function formattedChange(): string
    {
        if ($this->change === null) {
            return '—';
        }

        $changeFloat = (float) $this->change;
        $sign = $changeFloat > 0 ? '+' : ($changeFloat < 0 ? '-' : '');
        $absAmount = abs($changeFloat);

        $symbol = Currency::symbol($this->currency);
        $decimals = in_array(strtoupper($this->currency), ['TWD', 'NTD', 'VND'], true) ? 0 : 2;

        return $sign.$symbol.number_format($absAmount, $decimals);
    }

    /**
     * Formatted price change percent (e.g. +2.15% or -0.80%).
     */
    public function formattedChangePercent(): string
    {
        if ($this->changePercent === null) {
            return '—';
        }

        $percentFloat = (float) $this->changePercent;
        $sign = $percentFloat > 0 ? '+' : '';

        return $sign.number_format($percentFloat, 2).'%';
    }

    /**
     * Convert to an array for caching or serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'symbol' => $this->symbol,
            'name' => $this->name,
            'exchange' => $this->exchange,
            'price' => $this->price,
            'previous_close' => $this->previousClose,
            'change' => $this->change,
            'change_percent' => $this->changePercent,
            'volume' => $this->volume,
            'timestamp' => $this->timestamp?->toIso8601String(),
            'currency' => $this->currency,
        ];
    }

    /**
     * Reconstitute quote from array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            symbol: (string) $data['symbol'],
            exchange: isset($data['exchange']) ? (string) $data['exchange'] : null,
            price: (string) $data['price'],
            name: isset($data['name']) ? (string) $data['name'] : null,
            previousClose: isset($data['previous_close']) ? (string) $data['previous_close'] : null,
            change: isset($data['change']) ? (string) $data['change'] : null,
            changePercent: isset($data['change_percent']) ? (string) $data['change_percent'] : null,
            volume: isset($data['volume']) ? (string) $data['volume'] : null,
            timestamp: ! empty($data['timestamp']) ? Carbon::parse($data['timestamp']) : null,
            currency: isset($data['currency']) ? (string) $data['currency'] : 'TWD',
        );
    }
}
