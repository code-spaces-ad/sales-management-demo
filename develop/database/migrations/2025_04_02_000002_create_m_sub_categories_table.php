<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * サブカテゴリーマスターテーブル（m_sub_categories）作成
 */
class CreateMSubCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_sub_categories';
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');

            $table->unsignedInteger('code')
                ->comment('コード値');

            $table->unsignedInteger('category_id')
                ->comment('カテゴリーID');

            $table->string('name')
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
        DB::statement("ALTER TABLE $table COMMENT 'サブカテゴリーマスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_sub_categories');
    }
}
