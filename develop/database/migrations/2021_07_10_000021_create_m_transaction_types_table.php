<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterTransactionTypesConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 取引種別マスターテーブル（m_transaction_types）作成
 */
class CreateMTransactionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_transaction_types';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->tinyIncrements('id')
                ->comment('ID');
            $table->string('name', MasterTransactionTypesConst::NAME_MAX_LENGTH)
                ->comment('取引種別名');

            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');
            $table->dateTime('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日時');
            $table->dateTime('deleted_at')
                ->nullable()
                ->comment('削除日時');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '取引種別マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_transaction_types');
    }
}
