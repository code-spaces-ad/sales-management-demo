<?php

/**
 * PDF関連定数
 *
 * @copyright © 2025 CodeSpaces
 */

return [
    /** tempパス ※public_path */
    'temp_path' => 'tmp/',

    /** tempファイル削除期間 ※指定以前を削除 */
    'temp_deletion_period' => '-1 week',

    /**
     * LibreOffice 実行ファイル名
     */
    'exe_libre_office' => 'libreoffice',

    /** PDF変換時のコマンド付与（export HOME=/tmp;） */
    'use_home_option' => env('LIBREOFFICE_HOME_OPTION', true),

    /** ファイル名 */
    'filename' => [
        /** ファイル拡張子 */
        'file_extension' => '.pdf',
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
];
