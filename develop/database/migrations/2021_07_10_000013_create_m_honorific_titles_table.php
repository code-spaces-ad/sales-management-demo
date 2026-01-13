<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterHonorificTitleConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 敬称マスターテーブル（m_honorific_titles）作成
 */
class CreateMHonorificTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_honorific_titles';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->tinyIncrements('id')
                ->comment('ID');
            $table->unsignedTinyInteger('code')
                ->comment('コード値');
            $table->string('name', MasterHonorificTitleConst::NAME_MAX_LENGTH)
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
        $length = MasterHonorificTitleConst::CODE_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN code TINYINT($length) UNSIGNED COMMENT 'コード値'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '敬称マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_honorific_titles');
    }
}
