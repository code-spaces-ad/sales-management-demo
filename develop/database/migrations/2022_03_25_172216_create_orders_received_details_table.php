<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Receive\OrdersReceivedDetailConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 受注伝票詳細テーブル（purchase_order_details）作成
 */
class CreateOrdersReceivedDetailsTable extends Migration
{
    public $table = 'orders_received_details';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // テーブル作成
        Schema::create($this->table, function (Blueprint $table) {
            $table->unsignedBigInteger('orders_received_id')
                ->comment('伝票ID');
            $table->integer('product_id')
                ->default(0)
                ->comment('商品ID');
            $table->string('product_name', OrdersReceivedDetailConst::PRODUCT_NAME_MAX_LENGTH)
                ->comment('商品名');
            $table->decimal('quantity', 15, 4)
                ->default(0)
                ->comment('数量');
            $table->string('unit_name', OrdersReceivedDetailConst::UNIT_NAME_MAX_LENGTH)
                ->comment('単位');
            $table->decimal('unit_price', 15, 4)
                ->default(0)
                ->comment('単価');
            $table->integer('consumption_tax_rate')
                ->default(0)
                ->comment('消費税率');
            $table->boolean('reduced_tax_flag')
                ->default(false)
                ->comment('軽減税率対象フラグ');
            $table->unsignedTinyInteger('rounding_method_id')
                ->comment('消費税端数処理方法');
            $table->date('delivery_date')
                ->nullable()
                ->default(null)
                ->comment('納品日付');
            $table->string('note', OrdersReceivedDetailConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
            $table->tinyInteger('sales_confirm')
                ->nullable()
                ->default(null)
                ->comment('売上確定');
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
            $table->foreign('orders_received_id')
                ->references('id')
                ->on('orders_receiveds');
            $table->foreign('rounding_method_id')
                ->references('id')
                ->on('m_rounding_methods');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $this->table COMMENT '受注伝票詳細'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
