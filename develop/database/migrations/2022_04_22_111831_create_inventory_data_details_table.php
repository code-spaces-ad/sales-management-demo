<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Inventory\InventoryDataDetailConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 在庫データ詳細テーブル（inventory_data_details）作成
 */
class CreateInventoryDataDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'inventory_data_details';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_data_id')
                ->comment('在庫データID');
            $table->unsignedInteger('product_id')
                ->comment('商品ID');
            $table->string('product_name', InventoryDataDetailConst::PRODUCT_NAME_MAX_LENGTH)
                ->comment('商品名');
            $table->decimal('quantity', 15, 4)
                ->default(0)
                ->comment('数量');
            $table->string('note', InventoryDataDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
            $table->unsignedInteger('sort')
                ->comment('ソート');

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
            $table->foreign('inventory_data_id')
                ->references('id')
                ->on('inventory_datas');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '在庫データ詳細'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('inventory_data_details');
    }
}
