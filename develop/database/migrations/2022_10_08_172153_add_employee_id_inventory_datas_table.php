<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeIdInventoryDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_datas', function (Blueprint $table) {
            $table->unsignedInteger('employee_id')
                ->comment('担当者ID')
                ->after('to_warehouse_id');
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
            $table->dropColumn('employee_id');
        });
    }
}
