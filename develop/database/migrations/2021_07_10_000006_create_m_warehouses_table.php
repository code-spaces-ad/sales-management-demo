<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterWarehousesConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 倉庫マスターテーブル（m_warehouses）作成
 */
class CreateMWarehousesTable extends Migration
{
    /**
     * Run the migrations.s
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_warehouses';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedSmallInteger('code')
                ->comment('コード値');
            $table->string('name', MasterWarehousesConst::NAME_MAX_LENGTH)
                ->comment('名前');
            $table->string('name_kana', MasterWarehousesConst::NAME_KANA_MAX_LENGTH)
                ->nullable()
                ->comment('名前かな');

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
        $length = MasterWarehousesConst::CODE_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN code INT($length) UNSIGNED NOT NULL COMMENT 'コード値'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '倉庫マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_warehouses');
    }
}
