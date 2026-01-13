<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Sale\SalesOrderDetailConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 得意先_商品リレーションテーブル（m_customers_products）作成
 */
class CreateMCustomersProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_customers_products';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedInteger('customer_id')
                ->comment('得意先ID');
            $table->unsignedInteger('product_id')
                ->comment('商品ID');
            $table->string('unit_name', SalesOrderDetailConst::UNIT_NAME_MAX_LENGTH)
                ->comment('単位');
            $table->decimal('last_unit_price', 15, 4)
                ->default(0)
                ->comment('最終単価');

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
            $table->foreign('product_id')
                ->references('id')
                ->on('m_products');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '得意先_商品リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_customers_products');
    }
}
