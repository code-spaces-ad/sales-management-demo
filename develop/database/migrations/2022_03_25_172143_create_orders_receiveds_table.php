<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Receive\OrdersReceivedConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 受注伝票テーブル（orders_receiveds）作成
 */
class CreateOrdersReceivedsTable extends Migration
{
    public $table = 'orders_receiveds';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // テーブル作成
        Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedInteger('order_number')
                ->comment('伝票番号');
            $table->date('estimate_date')
                ->nullable()
                ->default(null)
                ->comment('見積日付');
            $table->date('order_date')
                ->nullable()
                ->default(null)
                ->comment('受注日付');
            $table->tinyInteger('order_status')
                ->unsigned()
                ->comment('納品状況');
            $table->unsignedInteger('customer_id')
                ->comment('請求先ID');
            $table->unsignedInteger('customer_delivery_id')
                ->nullable()
                ->comment('納品先ID');
            $table->unsignedInteger('branch_id')
                ->nullable()
                ->comment('支所ID');
            $table->unsignedInteger('employee_id')
                ->nullable()
                ->comment('担当者ID');
            $table->integer('sales_total')
                ->default(0)
                ->comment('売上合計');
            $table->integer('discount')
                ->default(0)
                ->comment('値引');
            $table->unsignedInteger('updated_id')
                ->nullable()
                ->comment('更新者ID');

            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');
            $table->dateTime('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日時');
            $table->dateTime('deleted_at')
                ->nullable()
                ->comment('削除日時');

            // 外部キー設定
            $table->foreign('customer_id')
                ->references('id')
                ->on('m_customers');
            $table->foreign('customer_delivery_id')
                ->references('id')
                ->on('m_customers');
            $table->foreign('employee_id')
                ->references('id')
                ->on('m_employees');
            $table->foreign('updated_id')
                ->references('id')
                ->on('m_users');
            $table->foreign('branch_id')
                ->references('id')
                ->on('m_branches');
        });

        // 伝票番号の桁数指定
        $length = OrdersReceivedConst::ORDER_NUMBER_MAX_LENGTH;
        DB::statement("ALTER TABLE $this->table MODIFY COLUMN order_number INT($length) UNSIGNED COMMENT '伝票番号'");

        // テーブルコメント
        DB::statement("ALTER TABLE $this->table COMMENT '受注伝票'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists($this->table);
    }
}
