<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterUsersConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ユーザーマスターテーブル（m_users）作成
 */
class CreateMUsersTable extends Migration
{
    /**
     * Run the migrations.s
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_users';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedInteger('code')
                ->comment('コード値');
            $table->string('login_id', MasterUsersConst::LOGIN_ID_MAX_LENGTH)
                ->unique()
                ->comment('ログインID');
            $table->string('password', MasterUsersConst::PASSWORD_MAX_LENGTH)
                ->comment('ログインパスワード');
            $table->string('name', MasterUsersConst::NAME_MAX_LENGTH)
                ->comment('名前');
            $table->tinyInteger('role_id')
                ->unsigned()
                ->comment('権限');
            $table->string('email', MasterUsersConst::EMAIL_MAX_LENGTH)
                ->nullable()
                ->comment('メールアドレス');
            $table->string('note', MasterUsersConst::MEMO_MAX_LENGTH)
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

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT 'ユーザーマスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_users');
    }
}
