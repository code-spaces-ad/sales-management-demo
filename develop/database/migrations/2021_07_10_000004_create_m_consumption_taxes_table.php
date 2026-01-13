<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 消費税マスターテーブル（m_consumption_taxes）作成
 */
class CreateMConsumptionTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_consumption_taxes';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->tinyIncrements('id')
                ->comment('ID');
            $table->date('begin_date')
                ->comment('開始日');
            $table->tinyInteger('normal_tax_rate')
                ->comment('通常税率');
            $table->tinyInteger('reduced_tax_rate')
                ->nullable()
                ->comment('軽減税率');

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
        DB::statement("ALTER TABLE $table COMMENT '消費税マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_consumption_taxes');
    }
}
