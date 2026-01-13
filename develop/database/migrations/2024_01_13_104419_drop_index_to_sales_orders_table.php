<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropIndexToSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign('sales_orders_transaction_type_id_foreign');
            $table->dropIndex('sales_orders_transaction_type_id_foreign');

            $table->dropForeign('sales_orders_branch_id_foreign');
            $table->dropIndex('sales_orders_branch_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreign('transaction_type_id')
                ->references('id')
                ->on('m_transaction_types');

            $table->foreign('branch_id')
                ->references('id')
                ->on('m_branches');
        });
    }
}
