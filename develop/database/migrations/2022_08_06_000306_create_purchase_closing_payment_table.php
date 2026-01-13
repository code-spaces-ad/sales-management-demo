<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 仕入締データ_支払伝票リレーションテーブル（purchase_closing_payment）作成
 */
class CreatePurchaseClosingPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'purchase_closing_payment';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_closing_id')
                ->comment('仕入締データID');
            $table->unsignedBigInteger('payment_id')
                ->comment('支払伝票ID');

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
            $table->foreign('payment_id')
                ->references('id')
                ->on('payments');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '仕入締データ_支払伝票リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('purchase_closing_payment');
    }
}
