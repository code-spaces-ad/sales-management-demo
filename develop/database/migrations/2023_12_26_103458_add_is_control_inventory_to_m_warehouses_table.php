<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsControlInventoryToMWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_warehouses', function (Blueprint $table) {
            $table->tinyInteger('is_control_inventory')
                ->comment('在庫管理有無(しない:0、する:1)')
                ->after('name_kana');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_warehouses', function (Blueprint $table) {
            $table->dropColumn('is_control_inventory');
        });
    }
}
