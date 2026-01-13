<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductStatusToMProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->smallInteger('product_status')
                ->nullable()
                ->comment('商品区分')
                ->after('classification2_id');
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
            $table->dropColumn('status');
        });
    }
}
