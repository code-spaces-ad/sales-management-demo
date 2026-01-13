<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedInteger('department_id')
                ->default(1)
                ->comment('部門ID')
                ->after('billing_date');
            $table->unsignedInteger('office_facilities_id')
                ->default(1)
                ->comment('事業所ID')
                ->after('department_id');
            $table->unsignedInteger('sales_classification_id')
                ->default(\App\Enums\SalesClassification::CLASSIFICATION_SALE)
                ->comment('売上分類')
                ->after('transaction_type_id');
            $table->unsignedTinyInteger('link_pos')
                ->default(0)
                ->comment('POS連携データ')
                ->after('memo');
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
            $table->dropColumn('department_id');
            $table->dropColumn('office_facilities_id');
            $table->dropColumn('sales_classification_id');
            $table->dropColumn('link_pos');
        });
    }
}
