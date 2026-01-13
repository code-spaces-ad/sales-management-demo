<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToDepositOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deposit_orders', function (Blueprint $table) {
            $table->unsignedInteger('department_id')
                ->default(1)
                ->comment('部門ID')
                ->after('billing_customer_id');
            $table->unsignedInteger('office_facilities_id')
                ->default(1)
                ->comment('事業所ID')
                ->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deposit_orders', function (Blueprint $table) {
            $table->dropColumn('department_id');
            $table->dropColumn('office_facilities_id');
        });
    }
}
