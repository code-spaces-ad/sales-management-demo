<?php

/**
 * 在庫調整画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Inventory;

use App\Enums\SortTypes;
use App\Helpers\InventoryStockDataHelper;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Inventory\InventoryStockDataEditRequest;
use App\Http\Requests\Inventory\InventoryStockDataSearchRequest;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterWarehouse;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 在庫調整画面用コントローラー
 */
class InventoryStocksDataController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * InventoryStocksDataController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param InventoryStockDataSearchRequest $request
     * @return View
     */
    public function index(InventoryStockDataSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLInventoryKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [
                'products' => MasterProduct::query()->oldest('name_kana')->get(),
                'warehouses' => MasterWarehouse::query()->oldest('code')->get(),
                'categories' => MasterCategory::query()->oldest('id')->get(),
                'sort_types' => SortTypes::asSelectArray(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'inventory_stock_datas' => InventoryStockDataHelper::getInventoryStockData($search_condition_input_data),
            ],
            /** 在庫調整フラグ用セッションキー */
            'session_adjust_stocks_key' => $this->refAdjustStocksKey(),
            /** ajax post用url */
            'post_url' => route('ajax.inventory_value'),
        ];

        return view('inventory.inventory_stock_datas.index', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $productId
     * @param int $wareHouseId
     * @return View
     */
    public function edit(int $productId, int $wareHouseId): View
    {
        $inventoryStockData = InventoryStockData::getDataByProductWareHouse($productId, $wareHouseId);

        // 在庫調整のフラグが立っているか判定
        if (Session::get($this->refAdjustStocksKey())) {
            SessionHelper::forgetSessionForMismatchURL('*inventory/inventory_stock_datas*', $this->refURLInventoryKey());

            return view('inventory.inventory_stock_datas.edit', $this->sendDataInventoryStocksData($inventoryStockData, $productId, $wareHouseId));
        }

        SessionHelper::forgetSessionForMismatchURL('*inventory/inventory_stock_datas*', $this->refURLInventoryKey());

        return view('inventory.inventory_stock_datas.create_edit', $this->sendDataInventoryStocksData($inventoryStockData, $productId, $wareHouseId));
    }

    /**
     * @param InventoryStockDataEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(InventoryStockDataEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 在庫数_データ一覧
            $product = InventoryStockData::find($request->id);
            $product->inventory_stocks = $request->inventory_stocks ?? 0;
            $product->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(),
                config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $request->warehouse_name, $request->product_name);

        // 一覧画面へリダイレクト
        return redirect(route('inventory.inventory_stock_datas.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Excelダウンロード
     *
     * @param InventoryStockDataSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(InventoryStockDataSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.inventory_stock_data');
        $headings = [
            '倉庫名',
            '商品名',
            '在庫数',
        ];

        $inventory_stock_data = InventoryStockDataHelper::getSearchResult($search_condition_input_data, true);

        if ($inventory_stock_data->isEmpty()) {
            // 在庫調整データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('inventory.inventory_stock_datas.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $inventory_stock_data->exportExcel($filename, $headings);
    }
}
