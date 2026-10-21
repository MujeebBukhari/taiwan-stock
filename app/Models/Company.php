<?php

namespace App\Models;

use App\Support\Currency;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    public const DEFAULT_CURRENCY = 'TWD';

    public const DEFAULT_EXCHANGE = 'TWSE';

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => self::DEFAULT_CURRENCY,
        'exchange' => self::DEFAULT_EXCHANGE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'symbol',
        'name',
        'exchange',
        'sector',
        'currency',
        'last_price',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_price' => 'string',
        ];
    }

    /**
     * Get the currency symbol for display (e.g. NT$ for TWD, US$ for USD).
     */
    public function getCurrencySymbolAttribute(): string
    {
        return Currency::symbol($this->currency);
    }

    /**
     * Format a monetary amount using this company's currency.
     */
    public function formatCurrency(string|int|float $amount, ?int $decimals = null): string
    {
        return Currency::format($amount, $this->currency, $decimals);
    }

    /**
     * Determine if this company is traded on a Taiwanese exchange (TWSE or TPEx).
     */
    public function isTaiwanese(): bool
    {
        return in_array(strtoupper($this->exchange), ['TWSE', 'TPEX'], true);
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive companies.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to search companies by symbol or name.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = '%'.trim((string) $term).'%';

        return $query->where(function (Builder $q) use ($term) {
            $q->where('symbol', 'like', $term)
                ->orWhere('name', 'like', $term);
        });
    }

    /**
     * Prepared relationship: Future stock positions linked to this company.
     * Will reference App\Models\StockPosition once created in Phase 3.
     */
    public function positions()
    {
        return $this->hasMany(StockPosition::class, 'company_id');
    }

    /**
     * Prepared relationship: Future transactions associated with this company.
     * Will reference App\Models\StockTransaction once created in Phase 3.
     */
    public function transactions()
    {
        return $this->hasMany(StockTransaction::class, 'company_id');
    }
}
