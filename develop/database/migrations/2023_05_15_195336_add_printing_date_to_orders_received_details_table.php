<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrintingDateToOrdersReceivedDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_received_details', function (Blueprint $table) {
            $table->date('printing_date')
                ->nullable()
                ->comment('納品書出力日')
                ->after('sales_confirm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_received_details', function (Blueprint $table) {
            $table->dropColumn('printing_date');
        });
    }
}
