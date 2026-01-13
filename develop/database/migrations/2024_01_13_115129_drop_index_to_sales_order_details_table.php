<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropIndexToSalesOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->dropForeign('sales_order_details_product_id_foreign');
            $table->dropIndex('sales_order_details_product_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('m_products');
        });
    }
}
