<?php

/**
 * Excel関連定数
 *
 * @copyright © 2025 CodeSpaces
 */

use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

return [
    /**
     * デフォルトの用紙サイズ
     */
    'default_papersize' => PageSetup::PAPERSIZE_A4,     // A4サイズ

    /**
     * デフォルトの用紙の向き
     */
    'default_orientation' => PageSetup::ORIENTATION_PORTRAIT,  // 縦

    /** ファイル名 */
    'filename' => [
        /** 社員マスター用 */
        'employees' => '社員マスター.xlsx',
        /** 得意先マスター用 */
        'customers' => '得意先マスター.xlsx',
        /** 商品マスター用 */
        'products' => '商品マスター.xlsx',
        /** カテゴリーマスター用 */
        'categories' => 'カテゴリーマスター.xlsx',
        /** 支所マスター用 */
        'branches' => '支所マスター.xlsx',
        /** 納品先マスター用 */
        'recipients' => '納品先マスター.xlsx',
        /** ユーザーマスター用 */
        'users' => 'ユーザーマスター.xlsx',
        /** 仕入先マスター用 */
        'suppliers' => '仕入先マスター.xlsx',
        /** 得意先別単価マスター用 */
        'customer_price' => '得意先別単価マスター.xlsx',
        /** 得意先元帳用 */
        'ledger_customers' => '得意先元帳.xlsx',
        /** 商品台帳用 */
        'ledger_product' => '商品台帳.xlsx',
        /** 種別累計売上表用 */
        'ledger_category' => '種別累計売上表.xlsx',
        /** 年度別販売実績表用 */
        'ledger_fiscal_year' => '年度別販売実績表.xlsx',
        /** 入金台帳用 */
        'ledger_deposit' => '金種別入金一覧表.xlsx',
        /** 売掛台帳用 */
        'ledger_accounts_receivable_balance' => '売掛台帳.xlsx',
        /** 商品別売上表 */
        'ledger_sales_product' => '商品別売上表.xlsx',
        /** 得意先別売上表 */
        'ledger_sales_customer' => '得意先別売上表.xlsx',
        /** 社内振替用表 */
        'ledger_office_transfer' => '社内振替用表.xlsx',

        /** 請求一覧用（在庫管理） */
        'charge_data' => '請求一覧.xlsx',

        /** 請求書発行用（販売管理） */
        'sale_invoice_print' => '請求書.xlsx',
        /** 請求書発行用（受発注管理） */
        'trading_invoice_print' => '請求書.xlsx',
        /** 納品書発行用（販売管理） */
        'sale_delivery_slip_print' => '納品書.xlsx',
        /** 納品書発行用（受発注管理） */
        'delivery_slip_print' => '納品書.xlsx',

        /** 見積書発行用（受発注管理） */
        'estimate' => '見積書.xlsx',

        /** 仕入台帳用（仕入管理） */
        'ledger_purchase_orders' => '仕入台帳.xlsx',

        /** 在庫データ一覧用（在庫管理） */
        'inventory_data' => '在庫データ一覧.xlsx',
        /** 在庫データ一覧用（在庫管理） */
        'products_moving' => '商品移動履歴一覧.xlsx',
        /** 在庫調整用（在庫管理） */
        'inventory_stock_data' => '在庫調整.xlsx',

        /** 仕入締一覧用（在庫管理） */
        'purchase_closing_list' => '仕入締一覧.xlsx',
        'orders_received' => '受注伝票一覧.xlsx',

        /** ファイル拡張子 */
        'file_extension' => '.xlsx',
        /** 各店送料内訳 */
        'store_shipping_fee_breakdown' => '各店送料内訳',
        /** 売上明細一覧(売上日指定) */
        'sales_detail_list' => '売上明細一覧_売上日指定',
        /** 得意先・商品・日別売上集計表 */
        'summary_sales_by_customer_product_day' => '得意先・商品・日別売上集計表',
        /** 振込手数料 */
        'bank_transfer_fee' => '振込手数料',
        /** 入金伝票問い合わせ振込手数料 */
        'deposit_slip_inquiry_transfer_fee' => '入金伝票問い合わせ振込手数料',
        /** 仕分帳 */
        'journal' => '仕分帳',
        /** 売掛金仕訳帳CSV出力軽減 */
        'accounts_receivable_journal' => '売掛金仕訳帳CSV出力軽減',
        /** 得意先元帳 */
        'customer_ledger' => '得意先元帳',
        /** 得意先元帳(担当者別) */
        'customer_ledger_by_employee' => '得意先元帳_担当者別',

        /** 経費コード別買掛金一覧 */
        'accounts_payable_list_by_expense_code' => '経費コード別買掛金一覧',
        /** 仕入明細一覧(入荷日指定) */
        'purchase_details_list' => '仕入明細一覧_入荷日指定',
        /** 買掛金増減表 */
        'accounts_payable_increase_decrease_table' => '買掛金増減表',
        /** 仕入先元帳(締間)軽減 */
        'supplier_ledger' => '仕入先元帳_締間_軽減',
        /** 売掛残高一覧 */
        'accounts_receivable_balance_list' => '売掛残高一覧',
        /** 売掛残高一覧(税率ごと) */
        'accounts_receivable_balance_list_by_tax_rate' => '売掛残高一覧_税率ごと',

        /** 月間商品売上簿 */
        'monthly_sales_book' => '月間商品売上簿',
    ],

    /** tempパス ※storage_path */
    'temp_path' => 'app/excel/tmp/',

    /** tempファイル削除期間 ※指定以前を削除 */
    'temp_deletion_period' => '-1 week',

    /** テンプレートパス ※storage_path */
    'template_path' => 'app/excel/',

    /** テンプレートファイル名 */
    'template_file' => [
        /** 請求書発行用（受発注管理） */
        'trading_invoice_print' => 'template_trading_invoice_print.xlsx',
        /** 請求書発行用（販売管理）・パターン３横 */
        'sale_invoice_print' => 'template_sale_invoice_print.xlsx',
        /** 納品書発行用（販売管理） */
        'sale_delivery_slip_print' => 'template_delivery_slip_print_purchase.xlsx',
        /** 納品書発行用（受発注管理）*/
        'delivery_slip_print' => 'template_delivery_slip_print.xlsx',
        /** 納品書発行用（受注管理） */
        'sale_delivery_slip_print_non_unit_name' => 'template_delivery_slip_print_purchase_non_unit_name.xlsx',
        /** 見積書発行用（受発注管理） */
        'estimate' => 'template_estimate.xlsx',
        /** 得意先元帳用 */
        'ledger_customers' => 'template_ledger_customers.xlsx',
        /** 売掛台帳用 */
        'ledger_accounts_receivable_balance' => 'template_ledger_accounts_receivable_balance.xlsx',
        /** 入金台帳用 */
        'ledger_deposit' => 'template_ledger_deposit.xlsx',
        /** 商品台帳用 */
        'ledger_product' => 'template_ledger_product.xlsx',
        /** 種別累計売上表用 */
        'ledger_category' => 'template_ledger_category.xlsx',
        /** 年度別販売実績表用 */
        'ledger_fiscal_year' => 'template_ledger_fiscal_year.xlsx',
        /** 商品別売上表用 */
        'ledger_sales_product' => 'template_ledger_sales_product.xlsx',
        /** 得意先別売上表用 */
        'ledger_sales_customer' => 'template_ledger_sales_customer.xlsx',
        /** 社内振替用表用 */
        'ledger_office_transfer' => 'template_ledger_office_transfer.xlsx',
        /** 仕入台帳用 */
        'ledger_purchase_orders' => 'template_ledger_purchase_orders.xlsx',
        'orders_received' => 'template_orders_received.xlsx',

        /** 各店送料内訳 */
        'store_shipping_fee_breakdown' => 'template_store_shipping_fee_breakdown.xlsx',
        /** 売上明細一覧(売上日指定) */
        'sales_detail_list' => 'template_sales_detail_list.xlsx',
        /** 得意先・商品・日別売上集計表 */
        'summary_sales_by_customer_product_day' => 'template_summary_sales_by_customer_product_day.xlsx',
        /** 振込手数料 */
        'bank_transfer_fee' => 'template_bank_transfer_fee.xlsx',
        /** 入金伝票問い合わせ振込手数料 */
        'deposit_slip_inquiry_transfer_fee' => 'template_deposit_slip_inquiry_transfer_fee.xlsx',
        /** 仕分帳 */
        'journal' => 'template_journal.xlsx',
        /** 売掛金仕訳帳CSV出力軽減 */
        'accounts_receivable_journal' => 'template_accounts_receivable_journal.xlsx',
        /** 得意先元帳 */
        'customer_ledger' => 'template_customer_ledger.xlsx',
        /** 得意先元帳(担当者別) */
        'customer_ledger_by_employee' => 'template_customer_ledger_by_employee.xlsx',

        /** 経費コード別買掛金一覧 */
        'accounts_payable_list_by_expense_code' => 'template_accounts_payable_list_by_expense_code.xlsx',
        /** 仕入明細一覧(入荷日指定) */
        'purchase_details_list' => 'template_purchase_details_list.xlsx',
        /** 買掛金増減表 */
        'accounts_payable_increase_decrease_table' => 'template_accounts_payable_increase_decrease_table.xlsx',
        /** 仕入先元帳(締間)軽減 */
        'supplier_ledger' => 'template_supplier_ledger.xlsx',
        /** 売掛残高一覧 */
        'accounts_receivable_balance_list' => 'template_accounts_receivable_balance_list.xlsx',
        /** 売掛残高一覧(税率ごと) */
        'accounts_receivable_balance_list_by_tax_rate' => 'template_accounts_receivable_balance_list_by_tax_rate.xlsx',

        /** 月間商品売上簿 */
        'monthly_sales_book' => 'template_monthly_sales_book.xlsx',
    ],
    /** シール・マークパス ※storage_path */
    'fold_mark_path' => 'app/public/image/',
];
