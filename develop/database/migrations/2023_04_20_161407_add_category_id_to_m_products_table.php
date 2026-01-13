<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToMProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->unsignedInteger('category_id')
                ->nullable()
                ->comment('カテゴリーID')
                ->after('name_kana');
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
            $table->dropColumn('category_id');
        });
    }
}
