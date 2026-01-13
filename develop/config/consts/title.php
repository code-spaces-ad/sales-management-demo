<?php

/**
 * タイトル関連定数
 *
 * @copyright © 2025 CodeSpaces
 */

return [
    /** 受注 */
    'receive' => [
        'menu' => [
            'index' => '受注伝票一覧',
            'create' => '受注伝票入力',
            'edit' => '受注伝票編集',
        ],
    ],
    /** 売上 */
    'sale' => [
        'menu' => [
            'index' => '売上伝票一覧',
            'create' => '売上伝票入力',
            'edit' => '売上伝票編集',
            'deposit' => [
                'index' => '入金伝票一覧',
                'create' => '入金伝票入力',
                'edit' => '入金伝票編集',
            ],
            'sale_ledger_submenu' => [
                'customers' => '得意先元帳',
                'accounts_receivable_balance' => '売掛台帳',
                'products' => '商品台帳',
                'categories' => '種別累計売上表',
                'fiscal_year' => '年度別販売実績表',
                'sales_products' => '商品別売上表',
                'sales_customers' => '得意先別売上表',
                'deposits' => '金種別入金一覧表',
                'shipping_fee' => '各店送料内訳',
                'sales_list' => '売上明細一覧(売上日指定)',
                'sales_customers_products' => '得意先・商品・日別売上集計表',
                'transfer_fee' => '振込手数料',
                'accounts_receivable_balance_office' => '売掛残高一覧',
                'accounts_receivable_balance_tax' => '売掛残高一覧(税率ごと)',
                'charge_list' => '請求一覧',
            ],
        ],
    ],
    /** 仕入 */
    'trading' => [
        'menu' => [
            'index' => '仕入伝票一覧',
            'create' => '仕入伝票入力',
            'edit' => '仕入伝票編集',
            'payment' => [
                'index' => '支払伝票一覧',
                'create' => '支払伝票入力',
                'edit' => '支払伝票編集',
            ],
            'closing' => [
                'closing' => '仕入締処理',
                'index' => '仕入締一覧',
                'detail' => '仕入締明細一覧',
            ],
            'orders_sd' => '仕入台帳',
            'accounts_payable' => '経費ｺｰﾄﾞ別買掛金一覧',
            'purchase_list' => '仕入明細一覧(入荷日指定)',
            'payment_list' => '支払一覧',
            'accounts_payable_rise_and_fall' => '買掛金増減表',
            'suppliers' => '仕入先元帳(締間)　軽減',
        ],
    ],
    /** 請求 */
    'charge' => [
        'menu' => [
            'index' => '請求一覧',
            'detail' => '請求明細一覧',
            'closing' => '請求締処理',
            'invoice_print' => '請求書発行',
        ],
    ],
    /** 在庫 */
    'inventory' => [
        'menu' => [
            'index' => '在庫データ一覧',
            'create' => '在庫データ入力',
            'edit' => '在庫データ編集',
            'stock' => '在庫確認',
            'products_moving' => '商品移動履歴一覧',
            'stock_datas' => [
                'index' => '在庫調整',
                'create' => '在庫入出庫入力',
                'edit' => '在庫調整編集',
            ],
        ],
    ],
    /** マスター */
    'master' => [
        'menu' => [
            'accounting_codes' => [
                'index' => '経理コードマスター',
                'create' => '経理コードマスター登録',
                'edit' => '経理コードマスター編集',
            ],
            'customers' => [
                'index' => '得意先マスター',
                'create' => '得意先マスター登録',
                'edit' => '得意先マスター編集',
            ],
            'branches' => [
                'index' => '支所マスター',
                'create' => '支所マスター登録',
                'edit' => '支所マスター編集',
            ],
            'recipients' => [
                'index' => '納品先マスター',
                'create' => '納品先マスター登録',
                'edit' => '納品先マスター編集',
            ],
            'suppliers' => [
                'index' => '仕入先マスター',
                'create' => '仕入先マスター登録',
                'edit' => '仕入先マスター編集',
            ],
            'products' => [
                'index' => '商品マスター',
                'create' => '商品マスター登録',
                'edit' => '商品マスター編集',
            ],
            'categories' => [
                'index' => 'カテゴリーマスター',
                'create' => 'カテゴリーマスター登録',
                'edit' => 'カテゴリーマスター編集',
            ],
            'sub_categories' => [
                'index' => 'サブカテゴリーマスター',
                'create' => 'サブカテゴリーマスター登録',
                'edit' => 'サブカテゴリーマスター編集',
            ],
            'departments' => [
                'index' => '部門マスター',
                'create' => '部門マスター登録',
                'edit' => '部門マスター編集',
            ],
            'office_facilities' => [
                'index' => '事業所マスター',
                'create' => '事業所マスター登録',
                'edit' => '事業所マスター編集',
            ],
            'employees' => [
                'index' => '担当マスター',
                'create' => '担当マスター登録',
                'edit' => '担当マスター編集',
            ],
            'warehouses' => [
                'index' => '倉庫マスター',
                'create' => '倉庫マスター登録',
                'edit' => '倉庫マスター編集',
            ],
            'customer_price' => [
                'index' => '得意先別単価マスター',
                'create' => '得意先別単価マスター登録',
                'edit' => '得意先別単価マスター編集',
            ],
            'kinds' => [
                'index' => '種別マスター',
                'create' => '種別マスター登録',
                'edit' => '種別マスター編集',
            ],
            'sections' => [
                'index' => '管理部署マスター',
                'create' => '管理部署マスター登録',
                'edit' => '管理部署マスター編集',
            ],
            'classifications1' => [
                'index' => '分類1マスター',
                'create' => '分類1マスター登録',
                'edit' => '分類1マスター編集',
            ],
            'classifications2' => [
                'index' => '分類2マスター',
                'create' => '分類2マスター登録',
                'edit' => '分類2マスター編集',
            ],
            'summary_group' => [
                'index' => '集計グループマスター',
                'create' => '集計グループマスター登録',
                'edit' => '集計グループマスター編集',
            ],
        ],
    ],
    /** POSデータ連携 */
    'data_transfer' => [
        'menu' => [
            'send_data' => 'データ送信',
            'receive_data' => 'データ受信',
        ],
    ],

    /** システム */
    'system' => [
        'menu' => [
            'head_office_info' => '会社情報設定',
            'log_operations' => '操作履歴一覧',
            'users' => [
                'index' => 'ユーザーマスター',
                'create' => 'ユーザーマスター登録',
                'edit' => 'ユーザーマスター編集',
                'profile' => 'プロフィール',
            ],
            'settings' => '設定',
        ],
    ],

    /** 帳票出力 */
    'report_output' => [
        'menu' => [
            'store_shipping_fee_breakdown' => '各店送料内訳',
            'sales_detail_list' => '売上明細一覧(売上日指定)',
            'summary_sales_by_customer_product_day' => '得意先・商品・日別売上集計表',
            'bank_transfer_fee' => '振込手数料',
            'deposit_slip_inquiry_transfer_fee' => '入金伝票問い合わせ振込手数料',
            'journal' => '仕訳帳',
            'accounts_receivable_journal' => '売掛金仕訳帳CSV出力軽減',
            'customer_ledger' => '得意先元帳',
            'customer_ledger_by_employee' => '得意先元帳(担当者別)',

            'accounts_payable_list_by_expense_code' => '経費コード別買掛金一覧',
            'purchase_details_list' => '仕入明細一覧(入荷日指定)',
            'accounts_payable_increase_decrease_table' => '買掛金増減表',
            'supplier_ledger' => '仕入先元帳(締間)軽減',
            'accounts_receivable_balance_list' => '売掛残高一覧',
            'accounts_receivable_balance_list_by_tax_rate' => '売掛残高一覧(税率ごと)',

            'monthly_sales_book' => '月間商品売上簿',
        ],
    ],
];
