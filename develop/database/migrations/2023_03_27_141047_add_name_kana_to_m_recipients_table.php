<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameKanaToMRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_recipients', function (Blueprint $table) {
            $table->string('name_kana')
                ->nullable()
                ->comment('名前かな')
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
        Schema::table('m_recipients', function (Blueprint $table) {
            $table->dropColumn('name_kana');
        });
    }
}
