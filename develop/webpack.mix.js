const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// popper.js 404(Not Found) 回避
mix.sourceMaps().js('node_modules/popper.js/dist/popper.js', 'public/js').sourceMaps();
mix.js('resources/js/loadBefore.js', 'public/js').sourceMaps();
mix.js('resources/js/app.js', 'public/js')
    //共通JS(index)
    .js(
        [
            'resources/js/common/index/branch.js',
            'resources/js/common/index/customer.js',
            'resources/js/common/index/employee.js',
            'resources/js/common/index/product.js',
            'resources/js/common/index/recipient.js',
            'resources/js/common/index/supplier.js',
            'resources/js/common/index/warehouse.js',
            'resources/js/common/index/department.js',
            'resources/js/common/index/office_facility.js',
            'resources/js/common/index/input_month.js',
            'resources/js/common/index/summary_group.js',
        ],
        'public/js/common_index.js'
    )
    //共通JS(create_edit)
    .js(
        [
            'resources/js/common/create_edit/check_closed.js',
            'resources/js/common/create_edit/category.js',
            'resources/js/common/create_edit/sub_category.js',
            'resources/js/common/create_edit/check_pos.js',
        ],
        'public/js/common_create_edit.js'
    )
    //在庫処理JS
    .js(
        [
            'resources/js/inventory/ajaxInventoryStocksData.js',
            'resources/js/inventory/ajaxInventoryValue.js',
        ],
        'public/js/inventory.js'
    )
    .js('resources/js/inventory/ajaxInventoryStocksData.js', 'public/js/inventory')
    //ユーティリティJS
    .js(
        [
            'resources/js/util/clear_input.js',
            'resources/js/util/get_date.js',
            'resources/js/util/get_price.js',
            'resources/js/util/select2.js',
            'resources/js/util/input_code.js',
            'resources/js/util/changeQuantityType.js',
            'resources/js/util/changeToggleSwitchIfMinus.js',
            'resources/js/util/accordion.js',
            'resources/js/util/load_event.js',
            'resources/js/util/save_scroll_position.js',
            'resources/js/util/loading.js',
            'resources/js/util/excel.js',
            'resources/js/util/pdf.js',
            'resources/js/util/search.js',
            'resources/js/util/tax.js',
            'resources/js/util/pos_api.js',
        ],
        'public/js/util.js'
    )
    .sass('resources/sass/app.scss', 'public/css')
    // 共通CSS
    .sass('resources/assets/sass/common.scss', '../resources/assets/build/css/')
    .styles(
        [
            'resources/assets/build/css/common.css',
        ],
        'public/css/common.css'
    )
    // サイドバー用JS
    .js(
        [
            'resources/assets/js/sidebar.js',
        ],
        'public/js/sidebar.js'
    )
    // その他JS
    .js(
        [
            'resources/assets/js/multi_submit_prevention.js',
            'resources/assets/js/keyevent_change.js',
            'resources/assets/js/replace_kana.js',
        ],
        'public/js/app_etc.js'
    )
    // 受注伝票
    .js('resources/js/app/receive/orders_received/index.js', 'public/js/app/receive/orders_received/index.js')
    .js('resources/js/app/receive/orders_received/create_edit.js', 'public/js/app/receive/orders_received/create_edit.js')
    // 売上管理(共通)
    .js('resources/js/app/sale/index.js', 'public/js/app/sale/index.js')
    // 売上伝票
    .js('resources/js/app/sale/orders/index.js', 'public/js/app/sale/orders/index.js')
    .js('resources/js/app/sale/orders/create_edit.js', 'public/js/app/sale/orders/create_edit.js')
    // 入金伝票
    .js('resources/js/app/sale/deposits/index.js', 'public/js/app/sale/deposits/index.js')
    .js('resources/js/app/sale/deposits/create_edit.js', 'public/js/app/sale/deposits/create_edit.js')
    // 各種帳票
    //// 得意先元帳
    .js('resources/js/app/sale/ledger/customers/index.js', 'public/js/app/sale/ledger/customers/index.js')
    //// 売掛台帳
    .js('resources/js/app/sale/ledger/accounts_receivable_balance/index.js', 'public/js/app/sale/ledger/accounts_receivable_balance/index.js')
    //// 商品台帳
    .js('resources/js/app/sale/ledger/products/index.js', 'public/js/app/sale/ledger/products/index.js')
    //// 種別累計売上表台帳
    .js('resources/js/app/sale/ledger/categories/index.js', 'public/js/app/sale/ledger/categories/index.js')
    //// 年度別販売実績表
    .js('resources/js/app/sale/ledger/fiscal_year/index.js', 'public/js/app/sale/ledger/fiscal_year/index.js')
    //// 商品別売上表
    .js('resources/js/app/sale/ledger/sales_products/index.js', 'public/js/app/sale/ledger/sales_products/index.js')
    //// 得意先別売上表
    .js('resources/js/app/sale/ledger/sales_customers/index.js', 'public/js/app/sale/ledger/sales_customers/index.js')
    //// 金種別入金一覧表
    .js('resources/js/app/sale/ledger/deposits/index.js', 'public/js/app/sale/ledger/deposits/index.js')

    // 請求処理(共通)
    .js('resources/js/app/invoice/index.js', 'public/js/app/invoice/index.js')
    // 請求一覧
    .js('resources/js/app/invoice/charge/index.js', 'public/js/app/invoice/charge/index.js')
    // 請求明細一覧
    .js("resources/js/app/invoice/charge_detail/index.js", 'public/js/app/invoice/charge_detail/index.js')
    // 請求締処理
    .js('resources/js/app/invoice/charge_closing/index.js', 'public/js/app/invoice/charge_closing/index.js')
    // 請求書発行
    .js('resources/js/app/invoice/invoice_print/index.js', 'public/js/app/invoice/invoice_print/index.js')

    // 仕入処理(共通)
    .js('resources/js/app/trading/index.js', 'public/js/app/trading/index.js')
    // 仕入先元帳
    .js('resources/js/app/trading/ledger/supplier/index.js', 'public/js/app/trading/ledger/supplier/index.js')
    // 仕入伝票
    .js('resources/js/app/trading/purchase_orders/index.js', 'public/js/app/trading/purchase_orders/index.js')
    .js('resources/js/app/trading/purchase_orders/create_edit.js', 'public/js/app/trading/purchase_orders/create_edit.js')
    // 支払伝票
    .js('resources/js/app/trading/payments/index.js', 'public/js/app/trading/payments/index.js')
    .js('resources/js/app/trading/payments/create_edit.js', 'public/js/app/trading/payments/create_edit.js')
    // 各種帳票
    //// 仕入台帳
    .js('resources/js/app/trading/ledger/purchase_orders_sd/index.js', 'public/js/app/trading/ledger/purchase_orders_sd/index.js')

    // 仕入締処理(共通)
    .js('resources/js/app/purchase_invoice/index.js', 'public/js/app/purchase_invoice/index.js')
    // 仕入締処理
    .js('resources/js/app/purchase_invoice/purchase_closing/index.js', 'public/js/app/purchase_invoice/purchase_closing/index.js')
    // 仕入締一覧
    .js('resources/js/app/purchase_invoice/purchase_closing_list/index.js', 'public/js/app/purchase_invoice/purchase_closing_list/index.js')
    // 仕入締明細一覧
    .js('resources/js/app/purchase_invoice/purchase_closing_detail/index.js', 'public/js/app/purchase_invoice/purchase_closing_detail/index.js')
    // 在庫処理処理(共通)
    .js('resources/js/app/inventory/index.js', 'public/js/app/inventory/index.js')
    // 在庫データ
    .js('resources/js/app/inventory/inventory_datas/index.js', 'public/js/app/inventory/inventory_datas/index.js')
    .js('resources/js/app/inventory/inventory_datas/create_edit.js', 'public/js/app/inventory/inventory_datas/create_edit.js')
    // 商品移動履歴一覧
    .js('resources/js/app/inventory/products_moving/index.js', 'public/js/app/inventory/products_moving/index.js')
    // 在庫確認
    .js('resources/js/app/inventory/stocks/index.js', 'public/js/app/inventory/stocks/index.js')
    // 在庫調整
    .js('resources/js/app/inventory/inventory_stock_datas/index.js', 'public/js/app/inventory/inventory_stock_datas/index.js')
    .js('resources/js/app/inventory/inventory_stock_datas/create_edit.js', 'public/js/app/inventory/inventory_stock_datas/create_edit.js')

    // ■マスター
    .js('resources/js/app/master/create_edit.js', 'public/js/app/master/create_edit.js')
    .js('resources/js/app/master/index.js', 'public/js/app/master/index.js')
    // 得意先マスター
    .js('resources/js/app/master/customers/index.js', 'public/js/app/master/customers/index.js')
    .js('resources/js/app/master/customers/create_edit.js', 'public/js/app/master/customers/create_edit.js')
    // 支所マスター
    .js('resources/js/app/master/branches/index.js', 'public/js/app/master/branches/index.js')
    .js('resources/js/app/master/branches/create_edit.js', 'public/js/app/master/branches/create_edit.js')
    // 納品先マスター
    .js('resources/js/app/master/recipients/index.js', 'public/js/app/master/recipients/index.js')
    .js('resources/js/app/master/recipients/create_edit.js', 'public/js/app/master/recipients/create_edit.js')
    // 仕入先マスター
    .js('resources/js/app/master/suppliers/index.js', 'public/js/app/master/suppliers/index.js')
    .js('resources/js/app/master/suppliers/create_edit.js', 'public/js/app/master/suppliers/create_edit.js')
    // 商品マスター
    .js('resources/js/app/master/products/index.js', 'public/js/app/master/products/index.js')
    .js('resources/js/app/master/products/create_edit.js', 'public/js/app/master/products/create_edit.js')
    // カテゴリーマスター
    .js('resources/js/app/master/categories/index.js', 'public/js/app/master/categories/index.js')
    .js('resources/js/app/master/categories/create_edit.js', 'public/js/app/master/categories/create_edit.js')
    // 担当マスター
    .js('resources/js/app/master/employees/index.js', 'public/js/app/master/employees/index.js')
    .js('resources/js/app/master/employees/create_edit.js', 'public/js/app/master/employees/create_edit.js')
    // 倉庫マスター
    .js('resources/js/app/master/warehouses/index.js', 'public/js/app/master/warehouses/index.js')
    .js('resources/js/app/master/warehouses/create_edit.js', 'public/js/app/master/warehouses/create_edit.js')
    // 得意先別単価マスター
    .js('resources/js/app/master/customer_price/index.js', 'public/js/app/master/customer_price/index.js')
    .js('resources/js/app/master/customer_price/create_edit.js', 'public/js/app/master/customer_price/create_edit.js')
    // 操作履歴一覧
    .js('resources/js/app/system/log_operations/index.js', 'public/js/app/system/log_operations/index.js')
    // ユーザーマスター
    .js('resources/js/app/system/users/index.js', 'public/js/app/system/users/index.js')
    .js('resources/js/app/system/users/create_edit.js', 'public/js/app/system/users/create_edit.js')
    // 設定
    .js('resources/js/app/system/settings/index.js', 'public/js/app/system/settings/index.js')
    // 会社情報設定
    .js('resources/js/app/system/head_office_info/create_edit.js', 'public/js/app/system/head_office_info/create_edit.js')
    // 種別マスター
    .js('resources/js/app/master/kinds/index.js', 'public/js/app/master/kinds/index.js')
    .js('resources/js/app/master/kinds/create_edit.js', 'public/js/app/master/kinds/create_edit.js')
    // 管理部署マスター
    .js('resources/js/app/master/sections/index.js', 'public/js/app/master/sections/index.js')
    .js('resources/js/app/master/sections/create_edit.js', 'public/js/app/master/sections/create_edit.js')
    // 分類1マスター
    .js('resources/js/app/master/classifications1/index.js', 'public/js/app/master/classifications1/index.js')
    .js('resources/js/app/master/classifications1/create_edit.js', 'public/js/app/master/classifications1/create_edit.js')
    // 分類2マスター
    .js('resources/js/app/master/classifications2/index.js', 'public/js/app/master/classifications2/index.js')
    .js('resources/js/app/master/classifications2/create_edit.js', 'public/js/app/master/classifications2/create_edit.js')
    // サブカテゴリマスター
    .js('resources/js/app/master/sub_categories/index.js', 'public/js/app/master/sub_categories/index.js')
    .js('resources/js/app/master/sub_categories/create_edit.js', 'public/js/app/master/sub_categories/create_edit.js')
    // 経理コードマスター
    .js('resources/js/app/master/accounting_codes/index.js', 'public/js/app/master/accounting_codes/index.js')
    .js('resources/js/app/master/accounting_codes/create_edit.js', 'public/js/app/master/accounting_codes/create_edit.js')
    // 部門マスター
    .js('resources/js/app/master/departments/index.js', 'public/js/app/master/departments/index.js')
    .js('resources/js/app/master/departments/create_edit.js', 'public/js/app/master/departments/create_edit.js')
    // 事業所マスター
    .js('resources/js/app/master/office_facilities/index.js', 'public/js/app/master/office_facilities/index.js')
    .js('resources/js/app/master/office_facilities/create_edit.js', 'public/js/app/master/office_facilities/create_edit.js')
    // 集計グループマスター
    .js('resources/js/app/master/summary_group/index.js', 'public/js/app/master/summary_group/index.js')
    .js('resources/js/app/master/summary_group/create_edit.js', 'public/js/app/master/summary_group/create_edit.js')

    // サブディレクトリ使用
    .setResourceRoot(process.env.MIX_ROOT_DIRECTORY_NAME)

    // Laravel Echo エラー回避
    .webpackConfig({
        module: {
            rules: [
                {
                    test: /\.m?js$/,
                    // laravel-echo を含む node_modules を Babel に通す
                    exclude: (modulePath) => {
                        return /node_modules/.test(modulePath) &&
                            !/node_modules\/laravel-echo/.test(modulePath);
                    },
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                ['@babel/preset-env', {
                                    targets: {
                                        esmodules: true,
                                    }
                                }]
                            ]
                        }
                    }
                }
            ]
        }
    });
mix.copy('resources/css/custom.css', 'public/css/custom.css');

// キャッシュバスティング
mix.version();
