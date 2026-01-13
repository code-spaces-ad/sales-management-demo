<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountingCodeIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->unsignedInteger('accounting_code_id')
                ->nullable()
                ->comment('経理CD')
                ->after('amount_rounding_method_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->dropColumn('accounting_code_id');
        });
    }
}
