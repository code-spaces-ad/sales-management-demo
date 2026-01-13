<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 締在庫テーブル（inventory_data_closing）作成
 */
class CreateInventoryDataClosingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'inventory_data_closing';
        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedInteger('warehouse_id')
                ->comment('倉庫ID');
            $table->unsignedInteger('product_id')
                ->nullable()
                ->comment('商品ID');
            $table->string('closing_ym', 6)
                ->nullable()
                ->comment('締年月');
            $table->decimal('closing_stocks', 15, 4)
                ->default(0)
                ->comment('締在庫数');

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
        DB::statement("ALTER TABLE $table COMMENT '締在庫数'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_data_closing');
    }
}
