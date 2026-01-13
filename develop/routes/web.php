<?php

/**
 * ルーティング設定
 *
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\System\HeadOfficeInfoConst;
use App\Http\Controllers\Api\PosReceiveController;
use App\Http\Controllers\Api\PosSendController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Authのルーティング
Auth::routes([
    'register' => false,    // ユーザー登録機能 OFF
    'reset' => true,    // パスワードリセット ON
    'confirm' => false,    // パスワード確認用 OFF
    'verify' => false,    // メール確認用 OFF
]);

// フォールバックルート
Route::fallback(function () {
    if (Auth::check()) {
        // ログイン中は、404ページへ
        abort(404);
    }

    // 未ログインの場合は、ログイン画面へリダイレクト
    return redirect(route('login'));
});

// トップページ
Route::get(RouteServiceProvider::HOME, function () {
    if (!Auth::check()) {
        // 未ログインの場合は、ログイン画面へリダイレクト
        return redirect(route('login'));
    }

    // ダッシュボードへ
    return redirect(route('dashboard.index'));
});

// エラーページ表示
Route::get('error/{code}', function ($code) {
    if (Auth::check()) {
        // ログイン中は、対象エラーページへ
        abort($code);
    }

    // 未ログインの場合は、ログイン画面へリダイレクト
    return redirect(route('login'));
});

// autocomplete
Route::group(['prefix' => 'autocomplete', 'as' => 'autocomplete.'], function () {
    Route::post('list_recipient_name', 'AutocompleteController@getListRecipientName')->name('list_recipient_name');
    Route::post('list_recipient_name_kana', 'AutocompleteController@getListRecipientNameKana')->name('list_recipient_name_kana');
});

// visibility
Route::group(['prefix' => 'visibility_session', 'as' => 'visibility_session.'], function () {
    Route::get('set_orders_received', 'VisibilitySessionController@setOrdersReceived')->name('set_orders_received');
});

// Ajax
Route::group(['prefix' => 'ajax', 'as' => 'ajax.'], function () {
    Route::post('inventory_stock_data', 'Ajax\AjaxInventoryStocksDataController@setSession')
        ->name('inventory_stock_data');
    // 在庫数
    Route::post('inventory_value', 'Ajax\AjaxInventoryStocksDataController@setInventory')
        ->name('inventory_value');
    // 税率取得
    Route::get('get_tax_rate', function (Illuminate\Http\Request $request) {
        $date = $request->query('date');
        $tax_data = App\Helpers\TaxHelper::getTaxRate($date);

        return response()->json($tax_data);
    });
});

// ダッシュボード
Route::resource('dashboard', 'DashboardController',
    ['only' => ['index', 'store', 'create', 'edit', 'update']])->middleware('auth');

// // 受注処理
// Route::group(['prefix' => 'receive', 'as' => 'receive.'], function () {
//    Route::get('/', function () {
//        // ルートは、受注一覧画面へリダイレクト
//        return redirect(route('receive.orders_received.index'));
//    })->middleware('auth');
//
//    /** 受注一覧、受注入力 */
//    // 伝票コピー
//    Route::post('orders_received/copy_order', 'Receive\OrdersReceivedController@copyOrder')
//        ->name('orders_received.copy_order');
//    Route::resource(
//        'orders_received',
//        'Receive\OrdersReceivedController',
//        ['only' => ['index', 'store', 'create', 'edit', 'update', 'destroy']]
//    );
//
//    Route::get(
//        'orders_received/download_excel',
//        'Receive\OrdersReceivedController@downloadExcel'
//    )->name('orders_received.download_excel');
//    // 納品書PDF表示
//    Route::get(
//        'orders_received/show_pdf',
//        'Receive\OrdersReceivedController@showPdf'
//    )->name('orders_received.show_pdf');
//
//    // 登録して納品書PDF表示
//    Route::POST(
//        'orders_received/store_show_pdf',
//        'Receive\OrdersReceivedController@storeAndShowPdf'
//    )->name('orders_received.store_show_pdf');
//    // 更新して納品書PDF表示
//    Route::put(
//        'orders_received/update_show_pdf/{order}',
//        'Receive\OrdersReceivedController@updateAndShowPdf'
//    )->name('orders_received.update_show_pdf');
//
// });

// 売上管理
Route::group(['prefix' => 'sale', 'as' => 'sale.'], function () {
    Route::get('/', function () {
        // ルートは、売上伝票入力画面へリダイレクト
        return redirect(route('sale.orders.create'));
    })->middleware('auth');

    /** 売上伝票入力 */
    Route::resource(
        'orders',
        'Sale\OrderController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );
    // 伝票コピー
    Route::post('orders/copy_order', 'Sale\OrderController@copyOrder')->name('orders.copy_order');
    // 納品書Excelダウンロード
    Route::get(
        'orders/download_excel',
        'Sale\OrderController@downloadExcel'
    )->name('orders.download_excel');
    // 納品書PDF表示
    Route::get(
        'orders/show_pdf',
        'Sale\OrderController@showPdf'
    )->name('orders.show_pdf');

    // 登録して納品書PDF表示
    Route::POST(
        'orders/store_show_pdf',
        'Sale\OrderController@storeAndShowPdf'
    )->name('orders.store_show_pdf');
    // 更新して納品書PDF表示
    Route::put(
        'orders/update_show_pdf/{order}',
        'Sale\OrderController@updateAndShowPdf'
    )->name('orders.update_show_pdf');

    /** 入金伝票入力 */
    // 伝票コピー
    Route::post('deposits/copy_order', 'Sale\DepositController@copyOrder')->name('deposits.copy_order');
    Route::resource(
        'deposits',
        'Sale\DepositController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 各種帳票出力
    //    Route::group(['prefix' => 'ledger', 'as' => 'ledger.'], function () {
    //        Route::get('/', function () {
    //            // ルートは、売掛台帳画面へリダイレクト
    //            return redirect(route('sale.ledger.accounts_receivable_balance'));
    //        })->middleware('auth');
    //
    //        /** 得意先元帳 */
    //        Route::get(
    //            'customers/download_excel',
    //            'Sale\Ledger\CustomerController@downloadExcel'
    //        )->name('customers.download_excel');
    //        Route::get(
    //            'customers/show_pdf',
    //            'Sale\Ledger\CustomerController@showPdf'
    //        )->name('customers.show_pdf');
    //
    //        Route::get('/customers', 'Sale\Ledger\CustomerController@index')->name('customers');
    //
    //        /** 売掛台帳 */
    //        Route::get(
    //            'accounts_receivable_balance/download_excel',
    //            'Sale\Ledger\AccountReceivableBalanceController@downloadExcel'
    //        )->name('accounts_receivable_balance.download_excel');
    //        Route::get(
    //            'accounts_receivable_balance/show_pdf',
    //            'Sale\Ledger\AccountReceivableBalanceController@showPdf'
    //        )->name('accounts_receivable_balance.show_pdf');
    //
    //        Route::get('/accounts_receivable_balance', 'Sale\Ledger\AccountReceivableBalanceController@index')
    //            ->name('accounts_receivable_balance');
    //
    //        /** 商品台帳 */
    //        Route::get(
    //            'products/download_excel',
    //            'Sale\Ledger\ProductController@downloadExcel'
    //        )->name('products.download_excel');
    //        Route::get(
    //            'products/show_pdf',
    //            'Sale\Ledger\ProductController@showPdf'
    //        )->name('products.show_pdf');
    //
    //        Route::get('/products', 'Sale\Ledger\ProductController@index')->name('products');
    //
    //        /** 種別累計売上表 */
    //        Route::get(
    //            'categories/download_excel',
    //            'Sale\Ledger\CategoryController@downloadExcel'
    //        )->name('categories.download_excel');
    //        Route::get(
    //            'categories/show_pdf',
    //            'Sale\Ledger\CategoryController@showPdf'
    //        )->name('categories.show_pdf');
    //
    //        Route::get('/categories', 'Sale\Ledger\CategoryController@index')->name('categories');
    //
    //        /** 年度別販売実績表 */
    //        Route::get(
    //            'fiscal_year/download_excel',
    //            'Sale\Ledger\FiscalYearController@downloadExcel'
    //        )->name('fiscal_year.download_excel');
    //        Route::get(
    //            'fiscal_year/show_pdf',
    //            'Sale\Ledger\FiscalYearController@showPdf'
    //        )->name('fiscal_year.show_pdf');
    //
    //        Route::get('/fiscal_year', 'Sale\Ledger\FiscalYearController@index')->name('fiscal_year');
    //
    //        /** 入金台帳 */
    //        Route::get(
    //            'deposits/download_excel',
    //            'Sale\Ledger\DepositController@downloadExcel'
    //        )->name('deposits.download_excel');
    //        Route::get(
    //            'deposits/show_pdf',
    //            'Sale\Ledger\DepositController@showPdf'
    //        )->name('deposits.show_pdf');
    //
    //        Route::get('/deposits', 'Sale\Ledger\DepositController@index')->name('deposits');
    //
    //        /** 商品別売上表 */
    //        Route::get('/sales_products', 'Sale\Ledger\SalesProductController@index')->name('sales_products');
    //        Route::get(
    //            'sales_products/download_excel',
    //            'Sale\Ledger\SalesProductController@downloadExcel'
    //        )->name('sales_products.download_excel');
    //        Route::get(
    //            'sales_products/show_pdf',
    //            'Sale\Ledger\SalesProductController@showPdf'
    //        )->name('sales_products.show_pdf');
    //
    //        /** 得意先別売上表 */
    //        Route::get('/sales_customers', 'Sale\Ledger\SalesCustomerController@index')->name('sales_customers');
    //        Route::get(
    //            'sales_customers/download_excel',
    //            'Sale\Ledger\SalesCustomerController@downloadExcel'
    //        )->name('sales_customers.download_excel');
    //        Route::get(
    //            'sales_customers/show_pdf',
    //            'Sale\Ledger\SalesCustomerController@showPdf'
    //        )->name('sales_customers.show_pdf');
    //    });
});

// 請求処理
Route::group(['prefix' => 'invoice', 'as' => 'invoice.'], function () {
    Route::get('/', function () {
        // ルートは、請求一覧画面へリダイレクト
        return redirect(route('invoice.charge.index'));
    })->middleware('auth');

    /** 請求一覧 */
    Route::get('charge/index', 'Invoice\ChargeController@index')->name('charge.index');

    /** 請求明細一覧 */
    Route::get('charge_detail/index', 'Invoice\ChargeDetailController@index')->name('charge_detail.index');

    /**
     * 請求締処理
     *
     * @see ChargeClosingController
     */
    Route::resource('charge_closing', 'Invoice\ChargeClosingController')->shallow()->only(['index']);
    Route::post('charge_closing/store', 'Invoice\ChargeClosingController@store')->name('charge_closing.store');
    Route::post('charge_closing/cancel', 'Invoice\ChargeClosingController@cancel')->name('charge_closing.cancel');

    /** 請求一覧 */
    Route::get(
        'charge/download_excel',
        'Invoice\ChargeController@downloadExcel'
    )->name('charge.download_excel');

    /** 請求書発行 */
    Route::get(
        'invoice_print/download_excel',
        'Invoice\InvoicePrintController@downloadExcel'
    )->name('invoice_print.download_excel');

    Route::get(
        'invoice_print/show_pdf',
        'Invoice\InvoicePrintController@showPdf'
    )->name('invoice_print.show_pdf');

    Route::get('invoice_print/index', 'Invoice\InvoicePrintController@index')->name('invoice_print.index');
});

// 仕入管理
Route::group(['prefix' => 'trading', 'as' => 'trading.'], function () {
    Route::get('/', function () {
        // ルートは、仕入一覧画面へリダイレクト
        return redirect(route('trading.purchase_orders.index'));
    })->middleware('auth');

    /** 仕入一覧、仕入入力 */
    Route::resource(
        'purchase_orders',
        'Trading\PurchaseOrderController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    /**  支払いデータ入力 */
    Route::post('payments/copy_order', 'Trading\PurchasePaymentController@copyOrder')
        ->name('payments.copy_order');
    Route::resource(
        'payments',
        'Trading\PurchasePaymentController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 仕入台帳参照
    //    Route::group(['prefix' => 'ledger', 'as' => 'ledger.'], function () {
    //        Route::get(
    //            'purchase_orders/download_excel',
    //            'Trading\ledger\PurchaseOrderLedgerController@downloadExcel'
    //        )->name('purchase_orders_sd.download_excel');
    //        Route::get(
    //            'purchase_orders/show_pdf',
    //            'Trading\ledger\PurchaseOrderLedgerController@showPdf'
    //        )->name('purchase_orders_sd.show_pdf');
    //        Route::resource(
    //            'purchase_orders_sd',
    //            'Trading\ledger\PurchaseOrderLedgerController',
    //            ['only' => ['index']]
    //        );
    //    });
});

// 仕入締処理
Route::group(['prefix' => 'purchase_invoice', 'as' => 'purchase_invoice.'], function () {
    Route::get('/', function () {
        // ルートは、仕入一覧画面へリダイレクト
        return redirect(route('purchase_invoice.purchase_orders.index'));
    })->middleware('auth');

    /**
     * 仕入締処理
     *
     * @see PurchaseClosingController
     */
    Route::resource('purchase_closing', 'PurchaseInvoice\PurchaseClosingController')->shallow()->only(['index']);
    Route::post('purchase_closing/store', 'PurchaseInvoice\PurchaseClosingController@store')->name('purchase_closing.store');
    Route::post('purchase_closing/cancel', 'PurchaseInvoice\PurchaseClosingController@cancel')->name('purchase_closing.cancel');

    /** 仕入締一覧 */
    Route::get('purchase_closing_list/index', 'PurchaseInvoice\PurchaseClosingListController@index')->name('purchase_closing_list.index');

    /** 仕入締一覧 */
    Route::get(
        'purchase_closing_list/download_excel',
        'PurchaseInvoice\PurchaseClosingListController@downloadExcel'
    )->name('purchase_closing_list.download_excel');

    /** 仕入締明細一覧 */
    Route::get('purchase_closing_detail/index', 'PurchaseInvoice\PurchaseClosingDetailController@index')->name('purchase_closing_detail.index');
});

// 在庫データ入力、在庫一覧・履歴
// 在庫処理
Route::group(['prefix' => 'inventory', 'as' => 'inventory.'], function () {
    // 在庫データ入力、在庫一覧・履歴
    // 伝票コピー
    Route::post('inventory/copy_order', 'Inventory\InventoryController@copyOrder')
        ->name('inventory.copy_order');
    // 在庫データ一覧Excelダウンロード
    Route::get(
        'inventory/download_excel',
        'Inventory\InventoryController@downloadExcel'
    )->name('inventory_datas.download_excel');
    Route::resource(
        'inventory_datas',
        'Inventory\InventoryController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );
    // 商品移動履歴一覧
    Route::resource(
        'products_moving',
        'Inventory\ProductsMovingController',
        ['only' => ['index']]
    );
    // 商品移動履歴一覧Excelダウンロード
    Route::get(
        'products_moving/download_excel',
        'Inventory\ProductsMovingController@downloadExcel'
    )->name('products_moving.download_excel');
    // 在庫データ確認
    Route::resource(
        'stocks',
        'Inventory\InventoryStocksController',
        ['only' => ['index']]
    );
    // 在庫調整
    // 在庫データ一覧Excelダウンロード
    Route::get(
        'inventory_stock_datas/download_excel',
        'Inventory\InventoryStocksDataController@downloadExcel'
    )->name('inventory_stock_datas.download_excel');
    Route::get(
        'inventory_stock_datas/edit/{product_id}/{warehouse_id}',
        'Inventory\InventoryStocksDataController@edit'
    )->name('inventory_stock_datas.edit');
    Route::resource(
        'inventory_stock_datas',
        'Inventory\InventoryStocksDataController',
        ['only' => ['index', 'update', 'destroy']]
    );
});

// 帳票管理
Route::group(['prefix' => 'report_output', 'as' => 'report_output.'], function () {
    Route::group(['prefix' => 'sale', 'as' => 'sale.'], function () {
        // 各店送料内訳
        Route::group(['prefix' => 'store_shipping_fee_breakdown', 'as' => 'store_shipping_fee_breakdown.'], function () {
            Route::get('/', 'ReportOutput\Sale\StoreShippingFeeBreakdownController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\StoreShippingFeeBreakdownController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\StoreShippingFeeBreakdownController@downloadPdf')->name('download_pdf');
        });
        // 売上明細一覧(売上日指定)
        Route::group(['prefix' => 'sales_detail_list', 'as' => 'sales_detail_list.'], function () {
            Route::get('/', 'ReportOutput\Sale\SalesDetailListController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\SalesDetailListController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\SalesDetailListController@downloadPdf')->name('download_pdf');
        });
        // 得意先・商品・日別売上集計表
        Route::group(['prefix' => 'summary_sales_by_customer_product_day', 'as' => 'summary_sales_by_customer_product_day.'], function () {
            Route::get('/', 'ReportOutput\Sale\SummarySalesByCustomerProductDayController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\SummarySalesByCustomerProductDayController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\SummarySalesByCustomerProductDayController@downloadPdf')->name('download_pdf');
        });
        // 振込手数料
        Route::group(['prefix' => 'bank_transfer_fee', 'as' => 'bank_transfer_fee.'], function () {
            Route::get('/', 'ReportOutput\Sale\BankTransferFeeController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\BankTransferFeeController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\BankTransferFeeController@downloadPdf')->name('download_pdf');
        });
        // 売掛残高一覧
        Route::group(['prefix' => 'accounts_receivable_balance_list', 'as' => 'accounts_receivable_balance_list.'], function () {
            Route::get('/', 'ReportOutput\Sale\AccountsReceivableBalanceListController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\AccountsReceivableBalanceListController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\AccountsReceivableBalanceListController@downloadPdf')->name('download_pdf');
        });
        // 売掛残高一覧(税率ごと)
        Route::group(['prefix' => 'accounts_receivable_balance_list_by_tax_rate', 'as' => 'accounts_receivable_balance_list_by_tax_rate.'], function () {
            Route::get('/', 'ReportOutput\Sale\AccountsReceivableBalanceListByTaxRateController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\AccountsReceivableBalanceListByTaxRateController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\AccountsReceivableBalanceListByTaxRateController@downloadPdf')->name('download_pdf');
        });
        // 得意先元帳
        Route::group(['prefix' => 'customer_ledger', 'as' => 'customer_ledger.'], function () {
            Route::get('/', 'ReportOutput\Sale\CustomerLedgerController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\CustomerLedgerController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\CustomerLedgerController@downloadPdf')->name('download_pdf');
        });
        // 得意先元帳(担当者別)
        Route::group(['prefix' => 'customer_ledger_by_employee', 'as' => 'customer_ledger_by_employee.'], function () {
            Route::get('/', 'ReportOutput\Sale\CustomerLedgerEmployeeController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Sale\CustomerLedgerEmployeeController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Sale\CustomerLedgerEmployeeController@downloadPdf')->name('download_pdf');
        });
    });

    Route::group(['prefix' => 'trading', 'as' => 'trading.'], function () {
        // 経費コード別買掛金一覧
        Route::group(['prefix' => 'accounts_payable_list_by_expense_code', 'as' => 'accounts_payable_list_by_expense_code.'], function () {
            Route::get('/', 'ReportOutput\Trading\AccountsPayableListByExpenseCodeController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Trading\AccountsPayableListByExpenseCodeController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Trading\AccountsPayableListByExpenseCodeController@downloadPdf')->name('download_pdf');
        });
        // 仕入明細一覧(入荷日指定)
        Route::group(['prefix' => 'purchase_details_list', 'as' => 'purchase_details_list.'], function () {
            Route::get('/', 'ReportOutput\Trading\PurchaseDetailsListController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Trading\PurchaseDetailsListController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Trading\PurchaseDetailsListController@downloadPdf')->name('download_pdf');
        });
        // 買掛金増減表
        Route::group(['prefix' => 'accounts_payable_increase_decrease_table', 'as' => 'accounts_payable_increase_decrease_table.'], function () {
            Route::get('/', 'ReportOutput\Trading\AccountsPayableIncreaseDecreaseTableController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Trading\AccountsPayableIncreaseDecreaseTableController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Trading\AccountsPayableIncreaseDecreaseTableController@downloadPdf')->name('download_pdf');
        });
        // 仕入先元帳(締間)軽減
        Route::group(['prefix' => 'supplier_ledger', 'as' => 'supplier_ledger.'], function () {
            Route::get('/', 'ReportOutput\Trading\SupplierLedgerController@index')->name('index');
            Route::get('download_excel', 'ReportOutput\Trading\SupplierLedgerController@downloadExcel')->name('download_excel');
            Route::get('download_pdf', 'ReportOutput\Trading\SupplierLedgerController@downloadPdf')->name('download_pdf');
        });
    });

    // 入金伝票問い合わせ振込手数料
    Route::group(['prefix' => 'deposit_slip_inquiry_transfer_fee', 'as' => 'deposit_slip_inquiry_transfer_fee.'], function () {
        Route::get('/', 'ReportOutput\DepositSlipInquiryTransferFeeController@index')->name('index');
        Route::get('download_excel', 'ReportOutput\DepositSlipInquiryTransferFeeController@downloadExcel')->name('download_excel');
        Route::get('download_pdf', 'ReportOutput\DepositSlipInquiryTransferFeeController@downloadPdf')->name('download_pdf');
    });
    // 仕訳帳
    Route::group(['prefix' => 'journal', 'as' => 'journal.'], function () {
        Route::get('/', 'ReportOutput\JournalController@index')->name('index');
        Route::get('download_excel', 'ReportOutput\JournalController@downloadExcel')->name('download_excel');
        Route::get('download_pdf', 'ReportOutput\JournalController@downloadPdf')->name('download_pdf');
    });
    // 売掛金仕訳帳CSV出力軽減
    Route::group(['prefix' => 'accounts_receivable_journal', 'as' => 'accounts_receivable_journal.'], function () {
        Route::get('/', 'ReportOutput\AccountsReceivableJournalController@index')->name('index');
        Route::get('download_excel', 'ReportOutput\AccountsReceivableJournalController@downloadExcel')->name('download_excel');
        Route::get('download_pdf', 'ReportOutput\AccountsReceivableJournalController@downloadPdf')->name('download_pdf');
    });
    // 月間商品売上簿
    Route::group(['prefix' => 'monthly_sales_book', 'as' => 'monthly_sales_book.'], function () {
        Route::get('/', 'ReportOutput\MonthlySalesBookController@index')->name('index');
        Route::get('download_excel', 'ReportOutput\MonthlySalesBookController@downloadExcel')->name('download_excel');
        Route::get('download_pdf', 'ReportOutput\MonthlySalesBookController@downloadPdf')->name('download_pdf');
    });
});

// マスター管理
Route::group(['prefix' => 'master', 'as' => 'master.'], function () {
    // 倉庫マスター
    Route::get(
        'accounting_codes/download_excel',
        'Master\MasterAccountingCodeController@downloadExcel'
    )->name('accounting_codes.download_excel');

    Route::resource(
        'accounting_codes',
        'Master\MasterAccountingCodeController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 仕入先マスター
    Route::get(
        'suppliers/download_excel',
        'Master\MasterSupplierController@downloadExcel'
    )->name('suppliers.download_excel');

    Route::resource(
        'suppliers',
        'Master\MasterSupplierController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 社員マスター
    Route::get(
        'employees/download_excel',
        'Master\MasterEmployeeController@downloadExcel'
    )->name('employees.download_excel');

    Route::resource(
        'employees',
        'Master\MasterEmployeeController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 得意先マスター
    Route::get(
        'customers/download_excel',
        'Master\MasterCustomerController@downloadExcel'
    )->name('customers.download_excel');

    Route::resource(
        'customers',
        'Master\MasterCustomerController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 支所マスター
    Route::get(
        'branches/download_excel',
        'Master\MasterBranchController@downloadExcel'
    )->name('branches.download_excel');

    Route::resource(
        'branches',
        'Master\MasterBranchController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 納品先マスター
    Route::get(
        'recipients/download_excel',
        'Master\MasterRecipientController@downloadExcel'
    )->name('recipients.download_excel');

    // 納品先マスター
    Route::resource(
        'recipients',
        'Master\MasterRecipientController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 商品マスター
    Route::get(
        'products/download_excel',
        'Master\MasterProductController@downloadExcel'
    )->name('products.download_excel');

    Route::resource(
        'products',
        'Master\MasterProductController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // カテゴリーマスター
    Route::get(
        'categories/download_excel',
        'Master\MasterCategoryController@downloadExcel'
    )->name('categories.download_excel');

    Route::resource(
        'categories',
        'Master\MasterCategoryController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // サブカテゴリマスター
    Route::get(
        'sub_categories/download_excel',
        'Master\MasterSubCategoryController@downloadExcel'
    )->name('sub_categories.download_excel');

    Route::resource(
        'sub_categories',
        'Master\MasterSubCategoryController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 倉庫マスター
    Route::get(
        'warehouses/download_excel',
        'Master\MasterWarehouseController@downloadExcel'
    )->name('warehouses.download_excel');

    Route::resource(
        'warehouses',
        'Master\MasterWarehouseController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 得意先別単価マスター
    Route::get(
        'customer_price/download_excel',
        'Master\MasterCustomerPriceController@downloadExcel'
    )->name('customer_price.download_excel');

    Route::resource(
        'customer_price',
        'Master\MasterCustomerPriceController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 種別マスター
    Route::get(
        'kinds/download_excel',
        'Master\MasterKindController@downloadExcel'
    )->name('kinds.download_excel');

    Route::resource(
        'kinds',
        'Master\MasterKindController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 管理部署マスター
    Route::get(
        'sections/download_excel',
        'Master\MasterSectionController@downloadExcel'
    )->name('sections.download_excel');

    Route::resource(
        'sections',
        'Master\MasterSectionController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 分類1マスター
    Route::get(
        'classifications1/download_excel',
        'Master\MasterClassification1Controller@downloadExcel'
    )->name('classifications1.download_excel');

    Route::resource(
        'classifications1',
        'Master\MasterClassification1Controller',
        [
            'only' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
            'parameters' => ['classifications1' => 'classification1'],
        ]
    );

    // 分類2マスター
    Route::get(
        'classifications2/download_excel',
        'Master\MasterClassification2Controller@downloadExcel'
    )->name('classifications2.download_excel');

    Route::resource(
        'classifications2',
        'Master\MasterClassification2Controller',
        [
            'only' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
            'parameters' => ['classifications2' => 'classification2'],
        ]
    );

    // 部門マスター
    Route::get(
        'departments/download_excel',
        'Master\MasterDepartmentController@downloadExcel'
    )->name('departments.download_excel');

    Route::resource(
        'departments',
        'Master\MasterDepartmentController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 事業所マスター
    Route::get(
        'office_facilities/download_excel',
        'Master\MasterOfficeFacilityController@downloadExcel'
    )->name('office_facilities.download_excel');

    Route::resource(
        'office_facilities',
        'Master\MasterOfficeFacilityController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 集計グループマスター
    Route::get(
        'summary_group/download_excel',
        'Master\MasterSummaryGroupController@downloadExcel'
    )->name('summary_group.download_excel');

    Route::resource(
        'summary_group',
        'Master\MasterSummaryGroupController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );
});

// POSデータ連携
Route::group(['prefix' => 'data_transfer', 'as' => 'data_transfer.'], function () {
    // データ送信
    Route::get('/send_data', 'DataTransfer\SendData\SendDataController@index')->name('send_data.index');
    // データ受信
    Route::get('/receive_data', 'DataTransfer\ReceiveData\ReceiveDataController@index')->name('receive_data.index');
});

// システム設定
Route::group(['prefix' => 'system', 'as' => 'system.'], function () {
    Route::get('/', function () {
        $company_id = HeadOfficeInfoConst::COMPANY_ID;    // 自社ID

        // ルートは、会社情報設定画面へリダイレクト
        return redirect(route('system.head_office_info.edit', $company_id));
    })->middleware('auth');

    // 会社情報設定
    Route::resource(
        'head_office_info',
        'System\HeadOfficeInfoController',
        ['only' => ['edit', 'update']]
    );

    // 操作履歴一覧
    Route::get('/log_operations', 'System\LogOperationController@index')->name('log_operations.index');
    Route::get(
        'log_operation/download_excel',
        'System\LogOperationController@downloadExcel'
    )->name('log_operation.download_excel');

    // ユーザーマスター
    Route::get(
        'users/download_excel',
        'System\MasterUserController@downloadExcel'
    )->name('users.download_excel');

    Route::resource(
        'users',
        'System\MasterUserController',
        ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]
    );

    // 設定
    Route::resource('settings', 'System\SettingsController')->only(['index', 'store']);
});

Route::get('pos_send/product', [PosSendController::class, 'sendProductMaster'])->name('pos_send.product');
Route::get('pos_send/customer', [PosSendController::class, 'sendCustomerMaster'])->name('pos_send.customer');
Route::get('pos_send/unit_price_customer', [PosSendController::class, 'sendUnitPriceByCustomerMaster'])->name('pos_send.unit_price_customer');
Route::get('pos_send/employee', [PosSendController::class, 'sendEmployeesMaster'])->name('pos_send.employee');

Route::get('pos_receive/sales', [PosReceiveController::class, 'receiveSales'])->name('pos_receive.sales');
Route::get('pos_receive/purchase', [PosReceiveController::class, 'receivePurchase'])->name('pos_receive.purchase');
Route::get('pos_receive/inventory', [PosReceiveController::class, 'receiveInventory'])->name('pos_receive.inventory');
Route::get('pos_receive/shipment', [PosReceiveController::class, 'receiveFactoryShipping'])->name('pos_receive.shipment');

// パスワードリセットのルート
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
