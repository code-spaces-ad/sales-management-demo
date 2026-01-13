<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseIdColumnToOrdersReceivedDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_received_details', function (Blueprint $table) {
            $table->unsignedInteger('warehouse_id')
                ->nullable()
                ->default(null)
                ->comment('倉庫ID')
                ->after('orders_received_id');
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
            $table->dropColumn('warehouse_id');
        });
    }
}
