<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Sale\DepositOrderBillConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 入金伝票_手形リレーションテーブル（deposit_order_bill）作成
 */
class CreateDepositOrderBillTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'deposit_order_bill';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('deposit_order_id')
                ->comment('入金伝票ID');
            $table->date('bill_date')
                ->nullable()
                ->comment('手形期日');
            $table->string('bill_number', DepositOrderBillConst::BILL_NUMBER_MAX_LENGTH)
                ->nullable()
                ->comment('手形番号');

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
            $table->foreign('deposit_order_id')
                ->references('id')
                ->on('deposit_orders');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '入金伝票_手形リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('deposit_order_bill');
    }
}
