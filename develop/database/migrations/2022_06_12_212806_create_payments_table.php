<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 支払伝票テーブル（payments）作成
 */
class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'payments';
        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedInteger('order_number')
                ->comment('伝票番号');
            $table->date('order_date')
                ->comment('伝票日付');
            $table->unsignedInteger('supplier_id')
                ->comment('仕入先ID');
            $table->integer('payment')
                ->default(0)
                ->comment('支払合計');
            $table->string('note', DepositOrderConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
            $table->dateTime('closing_at')
                ->nullable()
                ->default(null)
                ->comment('締処理日時');
            $table->text('memo')
                ->nullable()
                ->comment('メモ');
            $table->unsignedInteger('creator_id')
                ->nullable()
                ->comment('登録者ID');
            $table->unsignedInteger('updater_id')
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
            $table->foreign('supplier_id')
                ->references('id')
                ->on('m_suppliers');
        });

        // 伝票番号の桁数指定
        $length = DepositOrderConst::ORDER_NUMBER_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN order_number INT($length) UNSIGNED COMMENT '伝票番号'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '支払伝票'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('payments');
    }
}
