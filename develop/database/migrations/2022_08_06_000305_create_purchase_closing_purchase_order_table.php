<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 仕入締データ_仕入伝票リレーションテーブル（purchase_closing_purchase_order）作成
 */
class CreatePurchaseClosingPurchaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'purchase_closing_purchase_order';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_closing_id')
                ->comment('仕入締データID');
            $table->unsignedBigInteger('purchase_order_id')
                ->comment('仕入伝票ID');

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
            $table->foreign('purchase_closing_id')
                ->references('id')
                ->on('purchase_closing');
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '仕入締データ_仕入伝票リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('purchase_closing_purchase_order');
    }
}
