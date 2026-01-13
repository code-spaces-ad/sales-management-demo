<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_employees', function (Blueprint $table) {
            $table->unsignedInteger('department_id')
                ->nullable()
                ->comment('部門ID')
                ->after('hire_date');
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
            $table->dropColumn('department_id');
        });
    }
}
