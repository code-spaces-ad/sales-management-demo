<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiscalYearToMHeadOfficeInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_head_office_information', function (Blueprint $table) {
            $table->unsignedSmallInteger('fiscal_year')
                ->nullable()
                ->default(4)
                ->comment('会計年度')
                ->after('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_head_office_information', function (Blueprint $table) {
            $table->dropColumn('fiscal_year');
        });
    }
}
