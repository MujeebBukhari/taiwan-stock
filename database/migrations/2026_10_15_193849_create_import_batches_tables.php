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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('batch_number', 50)->unique();
            $table->string('filename');
            $table->string('file_path');
            $table->string('file_type', 10)->default('csv');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->string('status', 30)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'import_batches_user_created_idx');
        });

        Schema::create('import_batch_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_index');
            $table->string('symbol', 20)->nullable();
            $table->string('type', 10)->nullable();
            $table->string('quantity', 50)->nullable();
            $table->string('price', 50)->nullable();
            $table->string('transaction_date', 30)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('valid')->index();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('stock_transaction_id')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index(['import_batch_id', 'status'], 'import_rows_batch_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batch_rows');
        Schema::dropIfExists('import_batches');
    }
};
