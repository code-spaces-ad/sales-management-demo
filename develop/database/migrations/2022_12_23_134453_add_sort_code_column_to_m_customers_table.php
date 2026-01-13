<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortCodeColumnToMCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_customers', function (Blueprint $table) {
            $table->unsignedInteger('sort_code')
                ->comment('ソートコード')
                ->after('start_account_receivable_balance');
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
            $table->dropColumn('sort_code');
        });
    }


}
