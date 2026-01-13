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
        Schema::table('inventory_stock_datas', function (Blueprint $table) {
            $table->unsignedInteger('purchase_total_price')
                ->nullable()
                ->comment('仕入れ金額合計')
                ->after('inventory_stocks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_stock_datas', function (Blueprint $table) {
            $table->dropColumn('purchase_total_price');
        });
    }
};
