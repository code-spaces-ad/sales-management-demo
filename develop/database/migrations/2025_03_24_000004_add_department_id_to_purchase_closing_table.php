<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToPurchaseClosingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_closing', function (Blueprint $table) {
            $table->unsignedInteger('department_id')
                ->default(1)
                ->comment('部門ID')
                ->after('closing_date');
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
        Schema::table('purchase_closing', function (Blueprint $table) {
            $table->dropColumn('department_id');
            $table->dropColumn('office_facilities_id');
        });
    }
}
