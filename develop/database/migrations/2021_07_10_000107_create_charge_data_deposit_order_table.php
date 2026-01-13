<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 請求データ_入金伝票リレーションテーブル（charge_data_deposit_order）作成
 */
class CreateChargeDataDepositOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'charge_data_deposit_order';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('charge_data_id')
                ->comment('請求データID');
            $table->unsignedBigInteger('deposit_order_id')
                ->comment('入金伝票ID');

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
            $table->foreign('deposit_order_id')
                ->references('id')
                ->on('deposit_orders');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '請求データ_入金伝票リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('charge_data_deposit_order');
    }
}
