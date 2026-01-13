<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdersReceivedIdToInventoryDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_datas', function (Blueprint $table) {
            $table->unsignedBigInteger('orders_received_number')
                ->nullable()
                ->comment('受注番号')
                ->after('id');
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
            $table->dropColumn('orders_received_number');
        });
    }
}
