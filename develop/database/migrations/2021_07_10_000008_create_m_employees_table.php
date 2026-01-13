<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterEmployeesConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 社員マスターテーブル（m_employees）作成
 */
class CreateMEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_employees';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedInteger('code')
                ->comment('コード値');
            $table->string('name', MasterEmployeesConst::NAME_MAX_LENGTH)
                ->comment('名前');
            $table->string('name_kana', MasterEmployeesConst::NAME_KANA_MAX_LENGTH)
                ->nullable()
                ->comment('名前かな');
            $table->date('birthday')
                ->nullable()
                ->comment('生年月日');
            $table->date('hire_date')
                ->nullable()
                ->comment('入社日');
            $table->string('note', MasterEmployeesConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');

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
        $length = MasterEmployeesConst::CODE_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN code INT($length) UNSIGNED NOT NULL COMMENT 'コード値'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '社員マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_employees');
    }
}
