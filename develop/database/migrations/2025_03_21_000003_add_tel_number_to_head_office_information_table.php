<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTelNumberToHeadOfficeInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_head_office_information', function (Blueprint $table) {
            $table->string('tel_number', 20)
                ->comment('電話番号')
                ->change();

            $table->string('tel_number2', 20)
                ->nullable()
                ->comment('電話番号2')
                ->after('tel_number');

            $table->string('bank_account1', 50)
                ->nullable()
                ->comment('振込先1')
                ->after('fiscal_year');
            $table->string('bank_account2', 50)
                ->nullable()
                ->comment('振込先2')
                ->after('bank_account1');
            $table->string('bank_account3', 50)
                ->nullable()
                ->comment('振込先3')
                ->after('bank_account2');
            $table->string('bank_account4', 50)
                ->nullable()
                ->comment('振込先4')
                ->after('bank_account3');
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
            $table->dropColumn('tel_number');
            $table->string('tel_number', 13)
                ->comment('電話番号')
                ->after('address2');
            $table->dropColumn('tel_number2');
            $table->dropColumn('bank_account1');
            $table->dropColumn('bank_account2');
            $table->dropColumn('bank_account3');
            $table->dropColumn('bank_account4');
        });
    }
}
