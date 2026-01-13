<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Trading\PurchaseOrderDetailConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 仕入伝票詳細テーブル（purchase_order_details）作成
 */
class CreatePurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'purchase_order_details';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')
                ->comment('仕入伝票ID');
            $table->unsignedInteger('product_id')
                ->comment('商品ID');
            $table->string('product_name', PurchaseOrderDetailConst::PRODUCT_NAME_MAX_LENGTH)
                ->comment('商品名');
            $table->decimal('quantity', 15, 4)
                ->default(0)
                ->comment('数量');
            $table->string('unit_name', PurchaseOrderDetailConst::UNIT_NAME_MAX_LENGTH)
                ->comment('単位');
            $table->decimal('unit_price', 15, 4)
                ->default(0)
                ->comment('単価');
            $table->integer('sub_total')
                ->default(0)
                ->comment('小計金額');
            $table->integer('sub_total_tax')
                ->default(0)
                ->comment('小計税額');
            $table->unsignedTinyInteger('tax_type_id')
                ->default(1)
                ->comment('税区分');
            $table->integer('consumption_tax_rate')
                ->default(0)
                ->comment('消費税率');
            $table->boolean('reduced_tax_flag')
                ->default(false)
                ->comment('軽減税率対象フラグ');
            $table->unsignedTinyInteger('rounding_method_id')
                ->comment('消費税端数処理方法');
            $table->string('note', PurchaseOrderDetailConst::NOTE_MAX_LENGTH)
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
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders');
            $table->foreign('rounding_method_id')
                ->references('id')
                ->on('m_rounding_methods');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '仕入伝票詳細'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('purchase_order_details');
    }
}
