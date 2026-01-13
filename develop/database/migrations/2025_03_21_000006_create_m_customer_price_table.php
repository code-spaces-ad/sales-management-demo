<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 得意先別単価マスターテーブル（m_customer_price）作成
 */
class CreateMCustomerPriceTable extends Migration
{
    /**
     * Run the migrations.s
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_customer_price';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id',)
                ->comment('ID');
            $table->unsignedInteger('code')
                ->comment('コード値');
            $table->unsignedinteger('product_id')
                ->comment('商品ID');
            $table->unsignedinteger('customer_id')
                ->comment('得意先ID');
            $table->date('sales_date')
                ->nullable()
                ->comment('最終売上日');
            $table->decimal('sales_unit_price',15, 4)
                ->nullable()
                ->comment('最終売上単価');
            $table->decimal('tax_included',15, 4)
                ->comment('通常税率_税込単価');
            $table->decimal('reduced_tax_included',15, 4)
                ->comment('軽減税率_税込単価');
            $table->decimal('unit_price',15, 4)
                ->comment('税抜単価');
            $table->text('note')
                ->nullable()
                ->comment('備考');
            $table->datetime('created_at')
                ->comment('作成日時');
            $table->dateTime('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日時');
            $table->datetime('deleted_at')
                ->nullable()
                ->comment('削除日時');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '得意先別単価マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_customer_price');
    }
}
