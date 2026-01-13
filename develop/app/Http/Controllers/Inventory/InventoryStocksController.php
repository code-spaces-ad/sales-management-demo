<?php

/**
 * 在庫確認画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Inventory;

use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryStocksSearchRequest;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterProduct;
use Illuminate\View\View;

/**
 * 在庫確認画面用コントローラー
 */
class InventoryStocksController extends Controller
{
    /**
     * InventoryStocksController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param InventoryStocksSearchRequest $request
     * @return View
     */
    public function index(InventoryStocksSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 商品データ */
                'products' => MasterProduct::getProductData(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'inventory_stock_datas' => InventoryStockData::getSearchResultWhereNotIn($search_condition_input_data),
                'inventory_stock_total' => InventoryStockData::getInventoryStocksTotal($search_condition_input_data),
            ],
        ];

        return view('inventory.stocks.index', $data);
    }
}
