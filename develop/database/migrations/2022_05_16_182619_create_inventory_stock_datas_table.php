<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 現在庫テーブル（inventory_stock_datas）作成
 */
class CreateInventoryStockDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'inventory_stock_datas';

        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedInteger('warehouse_id')
                ->comment('倉庫ID');
            $table->unsignedInteger('product_id')
                ->nullable()
                ->comment('商品ID');
            $table->decimal('inventory_stocks', 15, 4)
                ->default(0)
                ->comment('現在庫数');
            $table->unsignedInteger('updated_id')
                ->nullable()
                ->comment('更新者ID');

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
            $table->foreign('updated_id')
                ->references('id')
                ->on('m_users');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '現在庫データ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('inventory_stock_datas');
    }
}
