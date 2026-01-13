<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 支払伝票詳細テーブル（payment_details）作成
 */
class CreatePaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'payment_details';
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')
                ->comment('支払伝票ID');

            $table->integer('amount_cash')
                ->default(0)
                ->comment('金額_現金');
            $table->integer('amount_check')
                ->default(0)
                ->comment('金額_小切手');
            $table->integer('amount_transfer')
                ->default(0)
                ->comment('金額_振込');
            $table->integer('amount_bill')
                ->default(0)
                ->comment('金額_手形');
            $table->integer('amount_offset')
                ->default(0)
                ->comment('金額_相殺');
            $table->integer('amount_discount')
                ->default(0)
                ->comment('金額_値引');
            $table->integer('amount_fee')
                ->default(0)
                ->comment('金額_手数料');
            $table->integer('amount_other')
                ->default(0)
                ->comment('金額_その他');

            $table->string('note_cash', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_現金');
            $table->string('note_check', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_小切手');
            $table->string('note_transfer', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_振込');
            $table->string('note_bill', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_手形');
            $table->string('note_offset', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_相殺');
            $table->string('note_discount', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_値引');
            $table->string('note_fee', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_手数料');
            $table->string('note_other', DepositOrderDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考_その他');

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
            $table->foreign('payment_id')
                ->references('id')
                ->on('payments');

        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '支払伝票詳細'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('payment_details');
    }
}
