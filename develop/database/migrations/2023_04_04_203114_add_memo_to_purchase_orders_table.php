<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMemoToPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('note');
            $table->text('memo')
                ->nullable()
                ->comment('メモ')
                ->after('closing_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('note', PurchaseOrderConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
            $table->dropColumn('memo');
        });
    }
}
