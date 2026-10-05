<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

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
        ];
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
