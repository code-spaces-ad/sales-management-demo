<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSummaryGroupIdToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_customers', function (Blueprint $table) {
            $table->unsignedInteger('summary_group_id')
                ->nullable()
                ->comment('集計グループ')
                ->after('code');
            $table->unsignedInteger('employee_id')
                ->nullable()
                ->comment('担当者ID')
                ->after('summary_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_customers', function (Blueprint $table) {
            $table->dropColumn('summary_group_id');
            $table->dropColumn('employee_id');
        });
    }
}
