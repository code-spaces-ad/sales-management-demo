<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 在庫状態履歴テーブル（inventory_data_status_history）作成
 */
class CreateInventoryDataHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'inventory_data_status_history';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedBigInteger('inventory_data_id')
                ->comment('在庫データID');
            $table->tinyInteger('inout_status')
                ->unsigned()
                ->comment('状態');
            $table->unsignedInteger('updated_id')
                ->nullable()
                ->comment('更新者ID');

            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');

            // 外部キー設定
            $table->foreign('inventory_data_id')
                ->references('id')
                ->on('inventory_datas');
            $table->foreign('updated_id')
                ->references('id')
                ->on('m_users');

            // インデックス
            $table->index(['updated_id']);
            $table->index(['inventory_data_id']);
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '在庫状態履歴'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_data_status_history');
    }
}
