<?php

/**
 * 初期値関連定数
 *
 * @copyright © 2025 CodeSpaces
 */

return [
    /** 共通 */
    'common' => [
        /** デフォルト税率 */
        'consumption_tax_rate' => 10,
        /** デフォルトMAX日付 */
        'default_max_date' => '9999-12-31',
        /** デフォルトMAX月 */
        'default_max_month' => '9999-12',

        /** 小計金額-端数処理 */
        'sub_total_rounding_method' => 3,

        /** 締日設定 */
        'closing_date_list' => [
            0 => '末',
            25 => '25',
            20 => '20',
            15 => '15',
            10 => '10',
            5 => '5',
        ],

        /** 登録/編集画面のクリアボタン表示 */
        'use_register_clear_button' => false,

        /** ログイン画面背景画像 ※ */
        'login_bg_images' => [
            'images/bg/top_bg.jpg', 'images/bg/top_bg2.jpg', 'images/bg/top_bg3.jpg',
        ],
    ],
    /** 売上伝票 */
    'sales_order' => [
        /** デフォルト取引種別 */
        'transaction_type_id' => 2, // 掛売
        /** 商品行デフォルト数 */
        'product_row_count' => 5,
        /** ページカウント */
        'page_count' => 20,
    ],
    /** 入金伝票 */
    'deposit_order' => [
        /** ページカウント */
        'page_count' => 20,
    ],
    /** 請求 */
    'charge' => [
        /** ページカウント */
        'page_count' => 20,
    ],
    /** 販売請求書発行 */
    'sales_invoice_print' => [
        /** 税額サマリー行様式(0:固定６行を出力／1:金額>0の行だけ出力) */
        'tax_summary_format' => 1,
        /** 行背景色（反転）ARGB */
        'row_back_color_reverse' => 'FFF6F6F6',
        /** 行前景色（内税明細）ARGB */
        'row_fore_color_normal' => 'FF000000',
        /** 行前景色（内税明細）ARGB */
        'row_fore_color_tax_in' => 'FF000000',
        /** 行前景色（非課税明細）ARGB */
        'row_fore_color_tax_free' => 'FF000000',
    ],
    /** 発注入力 */
    'purchase_order' => [
        /** 商品行デフォルト数 */
        'product_row_count' => 5,
    ],
    /** 受注入力 */
    'orders_received' => [
        /** 商品行デフォルト数 */
        'product_row_count' => 5,
    ],
    /** マスター */
    'master' => [
        /** 得意先マスタ */
        'customers' => [
            /** デフォルト税計算区分 */
            'tax_calc_type' => 1,
            /** デフォルト税額端数処理 */
            'tax_rounding_method' => 3,
            /** デフォルト金額端数処理 */
            'amount_rounding_method' => 3,
            /** デフォルト取引種別 */
            'transaction_type' => 2,
            /** デフォルト回収月 */
            'collection_month' => 2,
            /** デフォルト回収日 */
            'collection_day' => 31,
            /** デフォルト回収方法 */
            'collection_method' => 3,
            /** デフォルト請求書書式 */
            'sales_invoice_format_type' => 2,
            /** デフォルト請求書書式 */
            'sales_invoice_printing_method' => 1,
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 社員マスタ */
        'employees' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 支所マスタ */
        'branches' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 納品先マスタ */
        'recipients' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 商品マスタ */
        'products' => [
            /** デフォルト税区分 */
            'tax_type' => 1,
            /** デフォルト税率区分 */
            'reduced_tax_flag' => 0,
            /** デフォルト数量端数処理 */
            'quantity_rounding_method' => 2,
            /** デフォルト金額端数処理 */
            'amount_rounding_method' => 2,
            /** ページカウント */
            'page_count' => 20,
        ],
        /** ユーザーマスタ */
        'users' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 倉庫マスタ */
        'warehouses' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 種別マスタ */
        'kinds' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 管理部署マスタ */
        'sections' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 分類1マスタ */
        'classifications1' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 分類2マスタ */
        'classifications2' => [
            /** ページカウント */
            'page_count' => 20,
        ],
        /** 集計グループマスタ */
        'summary_group' => [
            /** ページカウント */
            'page_count' => 20,
        ],
    ],
    /** システム */
    'system' => [
        /** 操作履歴一覧 */
        'log_operations' => [
            /** ページカウント */
            'page_count' => 20,
        ],
    ],
    /** API */
    'api' => [
        /** Google住所参照URL */
        'google_address_search' => 'https://www.google.com/maps/search/',
    ],
];
