<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Inventory\InventoryDataConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 在庫データテーブル（inventory_datas）作成
 */
class CreateInventoryDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'inventory_datas';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->date('inout_date')
                ->nullable()
                ->default(null)
                ->comment('入出庫日付');
            $table->tinyInteger('inout_status')
                ->unsigned()
                ->comment('状態');
            $table->unsignedInteger('from_warehouse_id')
                ->nullable()
                ->comment('移動元倉庫ID');
            $table->unsignedInteger('to_warehouse_id')
                ->nullable()
                ->comment('移動先倉庫ID');
            $table->string('note', InventoryDataConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
            $table->unsignedInteger('updated_id')
                ->nullable()
                ->comment('更新者ID');

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
            $table->foreign('updated_id')
                ->references('id')
                ->on('m_users');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '在庫データ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('inventory_datas');
    }
}
