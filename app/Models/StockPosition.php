<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPosition extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_positions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'quantity',
        'average_price',
        'inventory_amount',
        'realized_profit',
        'last_transaction_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'average_price' => 'decimal:8',
            'inventory_amount' => 'decimal:8',
            'realized_profit' => 'decimal:8',
            'last_transaction_at' => 'datetime',
        ];
    }

    /**
     * The user who owns this stock position.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The company/stock held in this position.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to positions with positive remaining quantity.
     */
    public function scopeWithInventory(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope to closed/liquidated positions with zero or negative quantity.
     */
    public function scopeEmpty(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope to positions owned by a specific user.
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = is_object($user) ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Scope to positions for a specific company.
     */
    public function scopeForCompany(Builder $query, Company|int $company): Builder
    {
        $companyId = is_object($company) ? $company->id : $company;

        return $query->where('company_id', $companyId);
    }
}
