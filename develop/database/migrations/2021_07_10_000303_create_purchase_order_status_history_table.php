<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 仕入伝票状態履歴テーブル（purchase_order_status_history）作成
 */
class CreatePurchaseOrderStatusHistoryTable extends Migration
{
    /**
     * Run the migrations.s
     *
     * @return void
     */
    public function up()
    {
        $table = 'purchase_order_status_history';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedBigInteger('purchase_order_id')
                ->comment('仕入伝票ID');
            $table->tinyInteger('order_status')
                ->unsigned()
                ->comment('状態');
            $table->unsignedInteger('updated_id')
                ->nullable()
                ->comment('更新者ID');

            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');

            // 外部キー設定
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders');
            $table->foreign('updated_id')
                ->references('id')
                ->on('m_users');

            // インデックス
            $table->index(['updated_id']);
            $table->index(['purchase_order_id']);
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '仕入伝票状態履歴'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('purchase_order_status_history');
    }
}
