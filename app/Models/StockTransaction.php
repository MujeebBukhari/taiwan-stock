<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'quantity',
        'price',
        'amount',
        'cost',
        'realized_profit',
        'transaction_date',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'quantity' => 'decimal:8',
            'price' => 'decimal:8',
            'amount' => 'decimal:8',
            'cost' => 'decimal:8',
            'realized_profit' => 'decimal:8',
            'transaction_date' => 'date',
        ];
    }

    /**
     * The user who initiated and owns this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The company/stock associated with this transaction.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include BUY transactions.
     */
    public function scopeBuy(Builder $query): Builder
    {
        return $query->where('type', TransactionType::BUY);
    }

    /**
     * Scope a query to only include SELL transactions.
     */
    public function scopeSell(Builder $query): Builder
    {
        return $query->where('type', TransactionType::SELL);
    }

    /**
     * Scope a query to transactions for a specific company.
     */
    public function scopeForCompany(Builder $query, Company|int $company): Builder
    {
        $companyId = is_object($company) ? $company->id : $company;

        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to transactions owned by a specific user.
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = is_object($user) ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to transactions occurring between two dates.
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
