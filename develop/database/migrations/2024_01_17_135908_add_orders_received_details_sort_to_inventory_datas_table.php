<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdersReceivedDetailsSortToInventoryDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_datas', function (Blueprint $table) {
            $table->unsignedInteger('orders_received_details_sort')
                ->nullable()
                ->comment('受注詳細ソート')
                ->after('orders_received_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_datas', function (Blueprint $table) {
            $table->dropColumn('orders_received_details_sort');
        });
    }
}
