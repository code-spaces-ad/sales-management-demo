<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountToSalesOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->decimal('discount', 15, 4)
                ->default(0)
                ->comment('値引額')
                ->after('unit_price');
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
            $table->dropColumn('discount');
        });
    }
}
