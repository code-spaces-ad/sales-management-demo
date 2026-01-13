<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutputGroupToMAccountingCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_accounting_codes', function (Blueprint $table) {
            $table->unsignedInteger('output_group')
                ->default(0)
                ->comment('出力対象グループ')
                ->after('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_accounting_codes', function (Blueprint $table) {
            $table->dropColumn('output_group');
        });
    }
}
