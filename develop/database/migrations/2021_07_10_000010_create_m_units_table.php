<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterUnitConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 単位マスターテーブル（m_units）作成
 */
class CreateMUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_units';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->smallIncrements('id')
                ->comment('ID');
            $table->unsignedSmallInteger('code')
                ->comment('コード値');
            $table->string('name', MasterUnitConst::NAME_MAX_LENGTH)
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

        // コード値の桁数指定
        DB::statement("ALTER TABLE $table MODIFY COLUMN code SMALLINT(4) UNSIGNED COMMENT 'コード値'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '単位マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_units');
    }
}
