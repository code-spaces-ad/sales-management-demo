<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 端数処理方法マスターテーブル（m_rounding_methods）作成
 */
class CreateMRoundingMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_rounding_methods';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->tinyIncrements('id')
                ->comment('ID');
            $table->string('name', 10)
                ->comment('名前');

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
        DB::statement("ALTER TABLE $table COMMENT '端数処理方法マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_rounding_methods');
    }
}
