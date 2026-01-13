<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 仕入締データテーブル（purchase_closing）作成
 */
class CreatePurchaseClosingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'purchase_closing';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->date('purchase_closing_start_date')
                ->comment('仕入締開始日');
            $table->date('purchase_closing_end_date')
                ->comment('仕入締終了日');
            $table->unsignedInteger('supplier_id')
                ->comment('仕入先ID');
            $table->string('closing_ym', 6)
                ->nullable()
                ->comment('仕入締年月');
            $table->tinyinteger('closing_date')
                ->nullable()
                ->comment('仕入締日');
            $table->integer('before_purchase_total')
                ->default(0)
                ->comment('前回仕入額');
            $table->integer('payment_total')
                ->default(0)
                ->comment('今回支払額');
            $table->integer('adjust_amount')
                ->default(0)
                ->comment('調整額');
            $table->integer('carryover')
                ->default(0)
                ->comment('繰越残高');
            $table->integer('purchase_total')
                ->default(0)
                ->comment('今回仕入額');
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
            $table->integer('discount_total')
                ->default(0)
                ->comment('値引調整額');
            $table->integer('purchase_tax_total')
                ->default(0)
                ->comment('消費税額');
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
            $table->integer('purchase_closing_total')
                ->default(0)
                ->comment('今回支払額');
            $table->integer('purchase_order_count')
                ->default(0)
                ->comment('仕入伝票件数');
            $table->integer('payment_count')
                ->default(0)
                ->comment('支払伝票件数');
            $table->dateTime('planned_payment_at')
                ->nullable()
                ->comment('支払予定日');
            $table->integer('closing_user_id')
                ->nullable()
                ->comment('締処理ユーザーＩＤ');
            $table->dateTime('closing_at')
                ->nullable()
                ->comment('締処理日時');

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
        });


        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '仕入締データ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('purchase_closing');
    }
}
