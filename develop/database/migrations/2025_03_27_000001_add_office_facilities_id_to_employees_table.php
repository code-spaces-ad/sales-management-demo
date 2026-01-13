<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfficeFacilitiesIdToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_employees', function (Blueprint $table) {
            $table->unsignedInteger('office_facilities_id')
                ->nullable()
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
        Schema::table('m_employees', function (Blueprint $table) {
            $table->dropColumn('office_facilities_id');
        });
    }
}
