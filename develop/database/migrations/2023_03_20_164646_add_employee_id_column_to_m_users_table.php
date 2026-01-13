<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeIdColumnToMUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_users', function (Blueprint $table) {
            $table->unsignedInteger('employee_id')
                ->comment('担当者ID')
                ->nullable()
                ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_users', function (Blueprint $table) {
            $table->dropColumn('employee_id');
        });
    }
}
