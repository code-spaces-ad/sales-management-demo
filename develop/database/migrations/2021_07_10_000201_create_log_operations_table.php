<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 操作ログテーブル（log_operations）作成
 */
class CreateLogOperationsTable extends Migration
{
    /**
     * Run the migrations.s
     *
     * @return void
     */
    public function up()
    {
        $table = 'log_operations';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedInteger('user_id')
                ->nullable()
                ->comment('ユーザーID');
            $table->string('route_name')
                ->nullable()
                ->comment('ルート名');
            $table->string('request_url')
                ->nullable()
                ->comment('要求パス');
            $table->string('request_method')
                ->nullable()
                ->comment('要求メソッド');
            $table->unsignedInteger('status_code')
                ->nullable()
                ->comment('HTTPステータスコード');
            $table->text('request_message')
                ->nullable()
                ->comment('要求内容');
            $table->string('remote_addr')
                ->nullable()
                ->comment('クライアントIPアドレス');
            $table->string('user_agent')
                ->nullable()
                ->comment('ブラウザ名');
            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');

            // 外部キー設定
            $table->foreign('user_id')
                ->references('id')
                ->on('m_users');

            // インデックス
            $table->index(['user_id']);
            $table->index(['created_at']);
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '操作ログ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('log_operations');
    }
}
