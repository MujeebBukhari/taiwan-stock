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
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->decimal('fee', 20, 8)->default(0)->after('realized_profit');
            $table->decimal('tax', 20, 8)->default(0)->after('fee');
            $table->unsignedBigInteger('import_batch_id')->nullable()->after('notes')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn(['fee', 'tax', 'import_batch_id']);
        });
    }
};
