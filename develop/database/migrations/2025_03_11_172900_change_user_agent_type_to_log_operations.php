<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserAgentTypeToLogOperations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_operations',function (Blueprint $table){
            $table->text('user_agent')
                ->nullable()
                ->comment('ブラウザ名')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_operations',function (Blueprint $table){
            $table->string('user_agent')
                ->nullable()
                ->comment('ブラウザ名')
                ->change();
        });
    }
}
