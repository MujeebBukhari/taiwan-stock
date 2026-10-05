<?php

namespace App\Enums;

enum TransactionType: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';

    /**
     * Human-readable label for the transaction type.
     */
    public function label(): string
    {
        return match ($this) {
            self::BUY => 'Buy',
            self::SELL => 'Sell',
        };
    }

    /**
     * Determine whether the transaction is a BUY.
     */
    public function isBuy(): bool
    {
        return $this === self::BUY;
    }

    /**
     * Determine whether the transaction is a SELL.
     */
    public function isSell(): bool
    {
        return $this === self::SELL;
    }

    /**
     * Get all transaction type values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
