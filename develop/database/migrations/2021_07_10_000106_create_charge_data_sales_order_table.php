<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 請求データ_売上伝票リレーションテーブル（charge_data_sales_order）作成
 */
class CreateChargeDataSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'charge_data_sales_order';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('charge_data_id')
                ->comment('請求データID');
            $table->unsignedBigInteger('sales_order_id')
                ->comment('売上伝票ID');

            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');
            $table->dateTime('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日時');
            $table->dateTime('deleted_at')
                ->nullable()
                ->comment('削除日時');

            // 外部キー設定
            $table->foreign('charge_data_id')
                ->references('id')
                ->on('charge_data');
            $table->foreign('sales_order_id')
                ->references('id')
                ->on('sales_orders');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '請求データ_売上伝票リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('charge_data_sales_order');
    }
}
