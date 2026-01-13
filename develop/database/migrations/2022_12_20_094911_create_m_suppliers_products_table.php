<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMSuppliersProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_suppliers_products';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedInteger('supplier_id')
                ->comment('仕入先ID');
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
            $table->foreign('supplier_id')
                ->references('id')
                ->on('m_suppliers');
            $table->foreign('product_id')
                ->references('id')
                ->on('m_products');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '仕入先_商品リレーション'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_suppliers_products');
    }
}
