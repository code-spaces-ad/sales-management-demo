<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientIdToOrdersReceivedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_receiveds', function (Blueprint $table) {
            $table->unsignedInteger('recipient_id')
                ->nullable()
                ->default(null)
                ->comment('納品先ID')
                ->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_receiveds', function (Blueprint $table) {
            $table->dropColumn('recipient_id');
        });
    }
}
