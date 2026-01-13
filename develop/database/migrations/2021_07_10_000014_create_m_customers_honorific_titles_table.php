<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 得意先_敬称リレーションテーブル（m_customers_honorific_titles）作成
 */
class CreateMCustomersHonorificTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_customers_honorific_titles';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedInteger('customer_id')
                ->comment('得意先ID');
            $table->unsignedTinyInteger('honorific_title_id')
                ->comment('敬称ID');

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
            $table->foreign('customer_id')
                ->references('id')
                ->on('m_customers');
            $table->foreign('honorific_title_id')
                ->references('id')
                ->on('m_honorific_titles');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '得意先_敬称リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_customers_honorific_titles');
    }
}
