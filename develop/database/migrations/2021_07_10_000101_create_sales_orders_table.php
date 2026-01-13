<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Sale\SalesOrderConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 売上伝票テーブル（sales_orders）作成
 */
class CreateSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'sales_orders';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedInteger('order_number')
                ->comment('伝票番号');
            $table->date('order_date')
                ->comment('伝票日付');
            $table->date('billing_date')
                ->comment('請求日');
            $table->unsignedInteger('customer_id')
                ->comment('得意先ID');
            $table->unsignedInteger('billing_customer_id')
                ->nullable()
                ->comment('請求先ID');
            $table->unsignedInteger('branch_id')
                ->nullable()
                ->comment('支所ID');
            $table->unsignedTinyInteger('tax_calc_type_id')
                ->default(2)
                ->comment('税計算区分');
            $table->unsignedTinyInteger('transaction_type_id')
                ->comment('取引種別ID');
            $table->integer('sales_total')
                ->default(0)
                ->comment('売上合計');
            $table->integer('discount')
                ->default(0)
                ->comment('値引');
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
            $table->dateTime('closing_at')
                ->nullable()
                ->default(null)
                ->comment('締処理日時');
            $table->text('memo')
                ->nullable()
                ->comment('メモ');
            $table->unsignedInteger('creator_id')
                ->comment('登録者ID');
            $table->unsignedInteger('updater_id')
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
            $table->foreign('customer_id')
                ->references('id')
                ->on('m_customers');
            $table->foreign('transaction_type_id')
                ->references('id')
                ->on('m_transaction_types');
            $table->foreign('branch_id')
                ->references('id')
                ->on('m_branches');
        });

        // 伝票番号の桁数指定
        $length = SalesOrderConst::ORDER_NUMBER_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN order_number INT($length) UNSIGNED COMMENT '伝票番号'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '売上伝票'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('sales_orders');
    }
}
