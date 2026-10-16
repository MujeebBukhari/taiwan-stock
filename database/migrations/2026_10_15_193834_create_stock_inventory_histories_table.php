<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_inventory_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('stock_transaction_id')->constrained('stock_transactions')->cascadeOnDelete();
            $table->string('transaction_type', 10)->index();
            $table->date('transaction_date')->index();
            $table->decimal('quantity_before', 20, 8)->default(0);
            $table->decimal('transaction_quantity', 20, 8)->default(0);
            $table->decimal('quantity_after', 20, 8)->default(0);
            $table->decimal('executed_price', 20, 8)->default(0);
            $table->decimal('transaction_amount', 24, 8)->default(0);
            $table->decimal('wac_before', 20, 8)->default(0);
            $table->decimal('wac_after', 20, 8)->default(0);
            $table->decimal('inventory_amount_before', 24, 8)->default(0);
            $table->decimal('inventory_amount_after', 24, 8)->default(0);
            $table->decimal('cost_of_sold', 24, 8)->default(0);
            $table->decimal('realized_profit', 24, 8)->default(0);
            $table->timestamps();

            $table->unique('stock_transaction_id', 'tx_inventory_history_unique');
            $table->index(['user_id', 'company_id', 'transaction_date'], 'user_comp_inv_hist_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_inventory_histories');
    }
};
