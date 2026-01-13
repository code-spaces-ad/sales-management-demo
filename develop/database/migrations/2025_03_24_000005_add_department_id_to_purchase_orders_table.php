<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedInteger('department_id')
                ->default(1)
                ->comment('部門ID')
                ->after('closing_date');
            $table->unsignedInteger('office_facilities_id')
                ->default(1)
                ->comment('事業所ID')
                ->after('department_id');
            $table->unsignedInteger('transaction_type_id')
                ->default(\App\Enums\TransactionType::ON_ACCOUNT)
                ->comment('取引種別ID')
                ->after('office_facilities_id');
            $table->unsignedInteger('purchase_classification_id')
                ->default(\App\Enums\PurchaseClassification::CLASSIFICATION_PURCHASE)
                ->comment('仕入分類')
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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('department_id');
            $table->dropColumn('office_facilities_id');
            $table->dropColumn('transaction_type_id');
            $table->dropColumn('purchase_classification_id');
            $table->dropColumn('link_pos');
        });
    }
}
