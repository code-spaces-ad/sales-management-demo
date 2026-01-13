<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerProductCodeToMProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->string('customer_product_code', MasterProductsConst::CUSTOMER_PRODUCT_CODE_MAX_LENGTH)
                ->nullable()
                ->comment('相手先商品番号')
                ->after('name_kana');

            $table->string('jan_code', MasterProductsConst::JAN_CODE_MAX_LENGTH)
                ->nullable()
                ->comment('JANコード')
                ->after('customer_product_code');

            $table->unsignedInteger('sub_category_id')
                ->nullable()
                ->comment('サブカテゴリーID')
                ->after('category_id');

            $table->unsignedInteger('supplier_id')
                ->nullable()
                ->comment('仕入先ID')
                ->after('accounting_code_id');

            $table->string('note', MasterProductsConst::NAME_MAX_LENGTH)
                ->nullable()
                ->comment('備考')
                ->after('supplier_id');

            $table->string('specification', MasterProductsConst::NAME_MAX_LENGTH)
                ->nullable()
                ->comment('仕様')
                ->after('note');

            $table->unsignedInteger('kind_id')
                ->nullable()
                ->comment('種別ID')
                ->after('specification');

            $table->unsignedInteger('section_id')
                ->nullable()
                ->comment('管理部署ID')
                ->after('kind_id');

            $table->string('rack_address', MasterProductsConst::NAME_MAX_LENGTH)
                ->nullable()
                ->comment('棚番')
                ->after('section_id');

            $table->string('item_name', MasterProductsConst::NAME_MAX_LENGTH)
                ->nullable()
                ->comment('品名')
                ->after('rack_address');

            $table->decimal('purchase_unit_weight', 10, 4)
                ->nullable()
                ->comment('単重')
                ->after('item_name');

            $table->unsignedInteger('classification1_id')
                ->nullable()
                ->comment('分類1ID')
                ->after('purchase_unit_weight');

            $table->unsignedInteger('classification2_id')
                ->nullable()
                ->comment('分類2ID')
                ->after('classification1_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->dropColumn('customer_product_code');
            $table->dropColumn('jan_code');
            $table->dropColumn('sub_category_id');
            $table->dropColumn('supplier_id');
            $table->dropColumn('note');
            $table->dropColumn('specification');
            $table->dropColumn('kind_id');
            $table->dropColumn('section_id');
            $table->dropColumn('rack_address');
            $table->dropColumn('item_name');
            $table->dropColumn('purchase_unit_weight');
            $table->dropColumn('classification1_id');
            $table->dropColumn('classification2_id');
        });
    }
}
