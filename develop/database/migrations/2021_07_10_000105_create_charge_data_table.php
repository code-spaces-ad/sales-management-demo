<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 請求テーブル（charge_data）作成
 */
class CreateChargeDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'charge_data';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->date('charge_start_date')
                ->comment('請求開始日');
            $table->date('charge_end_date')
                ->comment('請求終了日');
            $table->unsignedInteger('customer_id')
                ->comment('得意先ID');
            $table->string('closing_ym', 6)
                ->nullable()
                ->comment('請求締年月');
            $table->tinyinteger('closing_date')
                ->nullable()
                ->comment('請求締日');
            $table->integer('before_charge_total')
                ->default(0)
                ->comment('前回請求額');
            $table->integer('payment_total')
                ->default(0)
                ->comment('今回入金額');
            $table->integer('adjust_amount')
                ->default(0)
                ->comment('調整額');
            $table->integer('carryover')
                ->default(0)
                ->comment('繰越残高');
            $table->integer('sales_total')
                ->default(0)
                ->comment('今回売上額');
            $table->integer('sales_total_normal_out')
                ->default(0)
                ->comment('今回売上額_通常税率_外税分');
            $table->integer('sales_total_reduced_out')
                ->default(0)
                ->comment('今回売上額_軽減税率_外税分');
            $table->integer('sales_total_normal_in')
                ->default(0)
                ->comment('今回売上額_通常税率_内税分');
            $table->integer('sales_total_reduced_in')
                ->default(0)
                ->comment('今回売上額_軽減税率_内税分');
            $table->integer('sales_total_free')
                ->default(0)
                ->comment('今回売上額_非課税分');
            $table->integer('discount_total')
                ->default(0)
                ->comment('値引調整額');
            $table->integer('sales_tax_total')
                ->default(0)
                ->comment('消費税額');
            $table->integer('sales_tax_normal_out')
                ->default(0)
                ->comment('消費税額_通常税率_外税分');
            $table->integer('sales_tax_reduced_out')
                ->default(0)
                ->comment('消費税額_軽減税率_外税分');
            $table->integer('sales_tax_normal_in')
                ->default(0)
                ->comment('消費税額_通常税率_内税分');
            $table->integer('sales_tax_reduced_in')
                ->default(0)
                ->comment('消費税額_軽減税率_内税分');
            $table->integer('charge_total')
                ->default(0)
                ->comment('今回請求額');
            $table->integer('sales_order_count')
                ->default(0)
                ->comment('売上伝票件数');
            $table->integer('deposit_order_count')
                ->default(0)
                ->comment('入金伝票件数');
            $table->dateTime('planned_deposit_at')
                ->nullable()
                ->comment('入金予定日');
            $table->unsignedTinyInteger('collection_method')
                ->nullable()
                ->comment('回収方法');
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
            $table->foreign('customer_id')
                ->references('id')
                ->on('m_customers');
        });


        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '請求データ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('charge_data');
    }
}
