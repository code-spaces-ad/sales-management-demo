<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Trading\PurchaseOrderConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 仕入先伝票テーブル（purchase_orders）作成
 */
class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'purchase_orders';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedInteger('order_number')
                ->comment('伝票番号');
            $table->date('order_date')
                ->nullable()
                ->default(null)
                ->comment('発注日付');
            $table->tinyInteger('order_status')
                ->unsigned()
                ->comment('状態');
            $table->unsignedInteger('supplier_id')
                ->comment('仕入先ID');
            $table->date('closing_date')
                ->nullable()
                ->default(null)
                ->comment('仕入締日');
            $table->integer('purchase_total')
                ->default(0)
                ->comment('仕入合計');
            $table->integer('discount')
                ->default(0)
                ->comment('値引');
            $table->integer('purchase_total_normal_out')
                ->default(0)
                ->comment('今回仕入額_通常税率_外税分');
            $table->integer('purchase_total_reduced_out')
                ->default(0)
                ->comment('今回仕入額_軽減税率_外税分');
            $table->integer('purchase_total_normal_in')
                ->default(0)
                ->comment('今回仕入額_通常税率_内税分');
            $table->integer('purchase_total_reduced_in')
                ->default(0)
                ->comment('今回仕入額_軽減税率_内税分');
            $table->integer('purchase_total_free')
                ->default(0)
                ->comment('今回仕入額_非課税分');
            $table->integer('purchase_tax_normal_out')
                ->default(0)
                ->comment('消費税額_通常税率_外税分');
            $table->integer('purchase_tax_reduced_out')
                ->default(0)
                ->comment('消費税額_軽減税率_外税分');
            $table->integer('purchase_tax_normal_in')
                ->default(0)
                ->comment('消費税額_通常税率_内税分');
            $table->integer('purchase_tax_reduced_in')
                ->default(0)
                ->comment('消費税額_軽減税率_内税分');
            $table->string('note', PurchaseOrderConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
            $table->dateTime('closing_at')
                ->nullable()
                ->default(null)
                ->comment('締処理日時');
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
            $table->foreign('supplier_id')
                ->references('id')
                ->on('m_suppliers');
            $table->foreign('updated_id')
                ->references('id')
                ->on('m_users');
        });

        // 伝票番号の桁数指定
        $length = PurchaseOrderConst::ORDER_NUMBER_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN order_number INT($length) UNSIGNED COMMENT '伝票番号'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '仕入伝票'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('purchase_orders');
    }
}
