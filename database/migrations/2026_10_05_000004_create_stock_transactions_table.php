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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('type', 10)->index();
            $table->decimal('quantity', 20, 8)->default(0);
            $table->decimal('price', 20, 8)->default(0);
            $table->decimal('amount', 24, 8)->default(0);
            $table->decimal('cost', 24, 8)->default(0);
            $table->decimal('realized_profit', 24, 8)->default(0);
            $table->date('transaction_date')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Financial query performance indexes
            $table->index(['user_id', 'company_id', 'transaction_date'], 'user_company_txdate_idx');
            $table->index(['user_id', 'type', 'transaction_date'], 'user_type_txdate_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
