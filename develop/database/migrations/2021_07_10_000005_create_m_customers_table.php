<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Enums\CollectionMonth;
use App\Enums\DepositMethodType;
use App\Enums\SalesInvoiceFormatType;
use App\Enums\SalesInvoicePrintingMethod;
use App\Enums\TransactionType;
use App\Consts\DB\Master\MasterCustomersConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 得意先マスターテーブル（m_customers）作成
 */
class CreateMCustomersTable extends Migration
{
    /**
     * Run the migrations.s
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_customers';
        // テーブル作成
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->string('code', MasterCustomersConst::CODE_MAX_LENGTH)
                ->comment('コード値');
            $table->string('name', MasterCustomersConst::NAME_MAX_LENGTH)
                ->comment('名前');
            $table->string('name_kana', MasterCustomersConst::NAME_KANA_MAX_LENGTH)
                ->nullable()
                ->comment('名前かな');

            $table->string('postal_code1', MasterCustomersConst::POSTAL_CODE1_MAX_LENGTH)
                ->nullable()
                ->comment('郵便番号1');
            $table->string('postal_code2', MasterCustomersConst::POSTAL_CODE2_MAX_LENGTH)
                ->nullable()
                ->comment('郵便番号2');
            $table->string('address1', MasterCustomersConst::ADDRESS1_MAX_LENGTH)
                ->comment('住所1');
            $table->string('address2', MasterCustomersConst::ADDRESS2_MAX_LENGTH)
                ->nullable()
                ->comment('住所2');
            $table->string('tel_number', MasterCustomersConst::TEL_NUMBER_MAX_LENGTH)
                ->nullable()
                ->comment('電話番号');
            $table->string('fax_number', MasterCustomersConst::FAX_NUMBER_MAX_LENGTH)
                ->nullable()
                ->comment('FAX番号');
            $table->string('email', MasterCustomersConst::EMAIL_MAX_LENGTH)
                ->nullable()
                ->comment('メールアドレス');
            $table->unsignedInteger('billing_customer_id')
                ->nullable()
                ->comment('請求先ID');
            $table->unsignedTinyInteger('tax_calc_type_id')
                ->default(2)
                ->comment('税計算区分');
            $table->unsignedTinyInteger('tax_rounding_method_id')
                ->comment('税額端数処理');
            $table->unsignedTinyInteger('transaction_type_id')
                ->default(TransactionType::ON_ACCOUNT)
                ->comment('取引種別ID');
            $table->unsignedTinyInteger('closing_date')
                ->comment('請求締日');
            $table->integer('start_account_receivable_balance')
                ->default(0)
                ->comment('開始売掛残高');
            $table->integer('billing_balance')
                ->default(0)
                ->comment('請求残高');
            $table->unsignedTinyInteger('collection_month')
                ->default(CollectionMonth::NEXT_MONTH)
                ->comment('回収月');
            $table->unsignedTinyInteger('collection_day')
                ->default(31)
                ->comment('回収日');
            $table->unsignedTinyInteger('collection_method')
                ->default(DepositMethodType::TRANSFER)
                ->comment('回収方法');
            $table->unsignedTinyInteger('sales_invoice_format_type')
                ->default(SalesInvoiceFormatType::NO_MIRROR)
                ->comment('請求書書式');
            $table->unsignedTinyInteger('sales_invoice_printing_method')
                ->default(SalesInvoicePrintingMethod::HORIZONTAL)
                ->comment('印刷方式');
            $table->string('note', MasterCustomersConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');

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
            $table->foreign('tax_rounding_method_id')
                ->references('id')
                ->on('m_rounding_methods');
        });

        // 自己参照の外部キー設定
        Schema::table($table, function (Blueprint $table) {
            $table->foreign('billing_customer_id')
                ->references('id')
                ->on('m_customers');
        });

        // コード値の桁数指定
        $length = MasterCustomersConst::CODE_MAX_LENGTH;
        DB::statement("ALTER TABLE $table MODIFY COLUMN code INT($length) UNSIGNED NOT NULL COMMENT 'コード値'");

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '得意先マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists('m_customers');
    }
}
