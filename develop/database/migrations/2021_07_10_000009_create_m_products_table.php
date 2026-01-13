<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterProductsConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 商品マスターテーブル（m_products）作成
 */
class CreateMProductsTable extends Migration
{
    /**
     * Run the migrations.s
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_products';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedInteger('code')
                ->comment('コード値');
            $table->string('name', MasterProductsConst::NAME_MAX_LENGTH)
                ->comment('名前');
            $table->string('name_kana', MasterProductsConst::NAME_KANA_MAX_LENGTH)
                ->nullable()
                ->comment('名前かな');
            $table->decimal('unit_price', 15, 4)
                ->default(0)
                ->comment('単価');
            $table->unsignedTinyInteger('tax_type_id')
                ->default(1)
                ->comment('税区分');
            $table->decimal('purchase_unit_price', 15, 4)
                ->default(0)
                ->comment('仕入単価');
            $table->boolean('reduced_tax_flag')
                ->default(0)
                ->comment('軽減税率対象フラグ');
            $table->unsignedTinyInteger('unit_price_decimal_digit')
                ->default(0)
                ->comment('単価小数桁数');
            $table->unsignedTinyInteger('quantity_decimal_digit')
                ->default(0)
                ->comment('数量小数桁数');
            $table->unsignedTinyInteger('quantity_rounding_method_id')
                ->comment('数量端数処理');
            $table->unsignedTinyInteger('amount_rounding_method_id')
                ->comment('金額端数処理');

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
            $table->foreign('quantity_rounding_method_id')
                ->references('id')
                ->on('m_rounding_methods');
            $table->foreign('amount_rounding_method_id')
                ->references('id')
                ->on('m_rounding_methods');
        });

        // コード値の桁数指定
        $length = MasterProductsConst::CODE_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN code INT($length) UNSIGNED NOT NULL COMMENT 'コード値'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '商品マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_products');
    }
}
