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
        Schema::create('stock_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->decimal('quantity', 20, 8)->default(0);
            $table->decimal('average_price', 20, 8)->default(0);
            $table->decimal('inventory_amount', 24, 8)->default(0);
            $table->decimal('realized_profit', 24, 8)->default(0);
            $table->dateTime('last_transaction_at')->nullable();
            $table->timestamps();

            // A user has exactly one position record per company
            $table->unique(['user_id', 'company_id'], 'user_company_position_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_positions');
    }
};
