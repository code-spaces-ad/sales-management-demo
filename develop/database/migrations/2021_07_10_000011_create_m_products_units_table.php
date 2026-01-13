<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 商品_単位リレーションテーブル（m_products_units）作成
 */
class CreateMProductsUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_products_units';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedInteger('product_id')
                ->comment('商品ID');
            $table->unsignedSmallInteger('unit_id')
                ->comment('単位ID');

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
            $table->foreign('product_id')
                ->references('id')
                ->on('m_products');
            $table->foreign('unit_id')
                ->references('id')
                ->on('m_units');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '商品_単位リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_products_units');
    }
}
