<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Http\Controllers\Ajax\AjaxChargeClosingController;
use App\Http\Controllers\Ajax\AjaxPurchaseClosingController;
use App\Http\Controllers\Api\PosReceiveController;
use App\Http\Controllers\Api\PosSendController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth'], 'as' => 'api.'], function () {
    // 請求締処理
    Route::post('invoice/closing_job', [AjaxChargeClosingController::class, 'closingJob'])
        ->name('invoice.closing_job');
    // 仕入締処理
    Route::post('purchase_invoice/closing_job', [AjaxPurchaseClosingController::class, 'closingJob'])
        ->name('purchase_invoice.closing_job');

    // 単価取得API(CodeSpacesでは使用しない)
    Route::get('order/get_unit_price', 'Sale\OrderController@getCustomerUnitPrice')
        ->name('order.get_unit_price');

    // 得意先別単価取得API
    Route::get('order/get_customer_price', 'Sale\OrderController@getCustomerPrice')
        ->name('order.get_customer_price');

    // 仕入単価取得API
    Route::get('purchase_order/get_unit_price', 'Trading\PurchaseOrderController@getSupplierUnitPrice')
        ->name('purchase_order.get_unit_price');

    // 仕入先別単価履歴取得API
    Route::get('purchase_order/get_unit_price_history', 'Trading\PurchaseOrderController@getSupplierUnitPriceHistory')
        ->name('purchase_order.get_unit_price_history');

    // 得意先別単価履歴取得API
    Route::get('sales_order/get_unit_price_history', 'Sale\OrderController@getCustomerUnitPriceHistory')
        ->name('sales_order.get_unit_price_history');

    // 売上管理：締処理実施判定API
    Route::get('charge_closing/is_closing', 'Invoice\ChargeClosingController@isClosing')
        ->name('charge_closing.is_closing');

    // 仕入管理：締処理実施判定API
    Route::get('purchase_closing/is_closing', 'PurchaseInvoice\PurchaseClosingController@isClosing')
        ->name('purchase_closing.is_closing');

    // 得意先別請求残高取得API
    Route::get('customers/get_billing_balance', 'Master\MasterCustomerController@getBillingBalance')
        ->name('customers.get_billing_balance');

    // 仕入先別支払残高取得API
    Route::get('suppliers/get_payment_balance', 'Master\MasterSupplierController@getPaymentBalance')
        ->name('suppliers.get_payment_balance');

    // マスタコード値空き番取得API
    Route::get('common/get_next_usable_code', 'Master\MasterSearchCodeController@getNextUsableCode')
        ->name('common.get_next_usable_code');
    // マスタソート_コード値空き番取得API
    Route::get('common/get_next_usable_sort_code', 'Master\MasterSearchCodeController@getNextUsableSortCode')
        ->name('common.get_next_usable_sort_code');

    // 住所検索
    Route::post('search_address', 'Api\SendController@searchAddress')
        ->name('search_address');
    // 空番検索
    Route::post('search_available_number', 'Api\SendController@searchAvailableNumber')
        ->name('search_available_number');
    // 空ソート番号検索
    Route::post('search_available_sort_number', 'Api\SendController@searchAvailableSortNumber')
        ->name('search_available_sort_number');
});

Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
    // 商品マスタ送信
    Route::post('/send_product_master', [PosSendController::class, 'sendProductMaster'])
        ->name('send_product_master');
    // 得意先マスタ送信
    Route::post('/send_customer_master', [PosSendController::class, 'sendCustomerMaster'])
        ->name('send_customer_master');
    // 得意先別単価マスタ送信
    Route::post('/send_unit_price_by_customer_master', [PosSendController::class, 'sendUnitPriceByCustomerMaster'])
        ->name('send_unit_price_by_customer_master');
    // 担当者マスタ送信
    Route::post('/send_employees_master', [PosSendController::class, 'sendEmployeesMaster'])
        ->name('send_employees_master');

    // 販売データ受信
    Route::post('/receive_sales', [PosReceiveController::class, 'receiveSales'])
        ->name('receive_sales');
    // 棚卸データ受信
    Route::post('/receive_inventory', [PosReceiveController::class, 'receiveInventory'])
        ->name('receive_inventory');
    // 工場出庫データ受信
    Route::post('/receive_factory_shipping', [PosReceiveController::class, 'receiveFactoryShipping'])
        ->name('receive_factory_shipping');
    // 仕入データ受信
    Route::post('/receive_purchase', [PosReceiveController::class, 'receivePurchase'])
        ->name('receive_purchase');
});
