<?php

/**
 * 在庫移動履歴一覧画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Inventory;

use App\Enums\StorehouseStatus;
use App\Helpers\DateHelper;
use App\Helpers\InventoryDataClosingHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Inventory\ProductsMovingSearchRequest;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataClosing;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterWarehouse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 在庫移動履歴一覧画面用コントローラー
 */
class ProductsMovingController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * InventoryController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param ProductsMovingSearchRequest $request
     * @return View
     */
    public function index(ProductsMovingSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLInventoryKey(), URL::full());

        /** 締年月 */
        $search_condition_input_data['closing_ym'] = null;
        if (isset($search_condition_input_data['inout_date']['start'])) {
            $search_condition_input_data['closing_ym'] = DateHelper::changeDateFormat(
                $search_condition_input_data['inout_date']['start'], 'Ym'
            );
        }

        // 倉庫の残数合計
        $stock_total = !isset($search_condition_input_data['warehouse_id'])
            /** 倉庫IDが指定されていなければ、在庫管理しない倉庫IDの加算・減算 の合計を取得 */
            ? InventoryData::getStockTotal($search_condition_input_data)
            /** 倉庫IDが指定されていれば、その倉庫の合計を取得 */
            : InventoryData::getStockTotalByWarehouse($search_condition_input_data);

        // 締在庫数の合計を取得(前月分)
        $closing_stock = InventoryDataClosing::getClosingStocksTotalFromLastMonth($search_condition_input_data) ?? 0;

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 倉庫データ */
                'warehouses' => MasterWarehouse::query()->oldest('code')->get(),
                /** 商品データ */
                'products' => MasterProduct::getProductData(),
                /** 状態 */
                'Storehouse_Status' => StorehouseStatus::asSelectArray(),
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'inventory_datas' => InventoryDataClosingHelper::getProductMovingResult($search_condition_input_data),
                'inventory_data_closing_stocks' => $closing_stock + $stock_total,
                'inventory_data_closing' => $closing_stock,
                'inventory_stock_total' => InventoryStockData::getInventoryStocksTotal($search_condition_input_data),
            ],
        ];

        return view('inventory.products_moving.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param ProductsMovingSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(ProductsMovingSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.products_moving');
        $headings = [
            '納品日または在庫データ入力日',
            '得意先',
            '支所',
            '納品先',
            '数量',
            '担当者',
            '備考',
        ];

        $inventory_data = InventoryData::getSearchResultClosingStocks($search_condition_input_data);

        $filters = [
            /** 納品日または在庫データ入力日 */
            function ($inventory_data) {
                return Carbon::parse($inventory_data->inout_date)->format('Y/m/d');
            },
            /** 得意先 */
            function ($inventory_data) {
                if ($inventory_data->ordersReceived) {
                    return $inventory_data->ordersReceived->customer_name
                        . '(' . $inventory_data->from_warehouse_name . '/' . $inventory_data->to_warehouse_name . ')';
                }

                return $inventory_data->from_warehouse_name . '/' . $inventory_data->to_warehouse_name;
            },
            /** 支所 */
            function ($inventory_data) {
                if ($inventory_data->ordersReceived) {
                    return $inventory_data->ordersReceived->branch_name;
                }

                return '';
            },
            /** 納品先 */
            function ($inventory_data) {
                if ($inventory_data->ordersReceived) {
                    return $inventory_data->ordersReceived->recipient_name;
                }

                return '';
            },
            /** 数量 */
            function ($inventory_data) {
                return number_format($inventory_data->quantity);
            },
            /** 担当者 */
            function ($inventory_data) {
                return $inventory_data->employee_name;
            },
            /** 備考 */
            function ($inventory_data) {
                return $inventory_data->note;
            },
        ];

        if ($inventory_data->isEmpty()) {
            // 商品移動データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('inventory.products_moving.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $inventory_data->exportExcel($filename, $headings, $filters);
    }
}
