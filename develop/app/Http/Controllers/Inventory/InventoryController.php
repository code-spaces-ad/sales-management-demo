<?php

/**
 * 在庫処理用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Inventory;

use App\Helpers\LogHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Inventory\InventoryEditRequest;
use App\Http\Requests\Inventory\InventorySearchRequest;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataDetail;
use App\Services\Inventory\InventoryService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 在庫処理用コントローラー
 */
class InventoryController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected InventoryService $service;

    /**
     * InventoryController constructor.
     */
    public function __construct(InventoryService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param InventorySearchRequest $request
     * @return View
     */
    public function index(InventorySearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLInventoryKey(), URL::full());

        return view('inventory.inventory_datas.index', $this->service->index($search_condition_input_data));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new InventoryData();
        $select_order_id = Session::get('select_inventory_data_id');
        if ($select_order_id != '') {
            // 選択した伝票をコピー
            $target_record_data = InventoryData::query()->find($select_order_id);
            Session::forget('select_inventory_data_id');   // 選択した伝票IDをセッションから削除
        }

        SessionHelper::forgetSessionForMismatchURL('*inventory/inventory_datas*', $this->refURLInventoryKey());

        return view('inventory.inventory_datas.create_edit', $this->service->create($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param InventoryEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(InventoryEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        $route = route('inventory.inventory_datas.create');
        // 在庫入出庫画面の処理
        if ($request->inout_page) {
            $route = Session::get('reference_url.inventory_url');
        }

        // 編集画面へリダイレクト
        return redirect($route)->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param InventoryData $Inventory_data
     * @return View
     */
    public function edit(InventoryData $Inventory_data): View
    {
        return view('inventory.inventory_datas.create_edit', $this->service->edit($Inventory_data));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param InventoryEditRequest $request
     * @param InventoryData $inventory_data
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(InventoryEditRequest $request, InventoryData $inventory_data): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 在庫データを更新
            $inventory_data->inout_date = $request->inout_date;
            $inventory_data->inout_status = 1;
            $inventory_data->from_warehouse_id = $request->from_warehouse_id;
            $inventory_data->to_warehouse_id = $request->to_warehouse_id;
            $inventory_data->employee_id = $request->employee_id;
            $inventory_data->updated_id = Auth::user()->id;

            $inventory_data->save();

            // 元の在庫データ詳細を一部削除
            $detail_diff_count = count($inventory_data->InventoryDataDetail) - count($request->detail ?? []);
            if ($detail_diff_count > 0) {
                $inventory_data->inventoryDataDetail()
                    ->orderByDesc('sort')
                    ->take($detail_diff_count)
                    ->delete();
            }

            // 在庫データ詳細を更新
            $sort_index = 1;
            foreach ($request->detail ?? [] as $detail) {
                $inventory_detail = new InventoryDataDetail();
                $inventory_detail->inventory_data_id = $inventory_data->id;
                $inventory_detail->product_id = $detail['product_id'];
                $inventory_detail->product_name = $detail['product_name'];
                $inventory_detail->quantity = $detail['quantity'] ?? 0;
                $inventory_detail->note = $detail['note'];

                $inventory_detail_other = $inventory_detail->toArray();
                unset($inventory_detail_other['quantity_digit_cut']);
                unset($inventory_detail_other['unit_price_digit_cut']);

                // upsert で更新
                $inventory_data->inventoryDataDetail()
                    ->updateOrInsert(
                        [
                            'sort' => $sort_index,
                            'deleted_at' => null,
                        ],
                        $inventory_detail_other
                    );

                ++$sort_index;
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        $message = config('consts.message.common.update_success');
        if ($error_flag) {
            $message = config('consts.message.common.update_failed');
        }

        // 編集画面へリダイレクト
        return redirect(route('inventory.inventory_datas.edit', $inventory_data->id))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param InventoryData $inventory_data
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(InventoryData $inventory_data): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($inventory_data);

        // 在庫データ一覧画面へリダイレクト
        return redirect(route('inventory.inventory_datas.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Excelダウンロード
     *
     * @param InventorySearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(InventorySearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.inventory_data');
        $headings = [
            '入出庫日',
            '担当者',
            'どこから',
            'どこへ',
            '商品',
            '数量',
            '備考',
        ];

        $inventory_data = InventoryData::getSearchResultDetail($search_condition_input_data);

        $filters = [
            /** 入出庫日 */
            function ($inventory_data) {
                return Carbon::parse($inventory_data->inout_date)->format('Y/m/d');
            },
            /** 担当者 */
            function ($inventory_data) {
                return $inventory_data->employee_name;
            },
            /** どこから */
            function ($inventory_data) {
                return $inventory_data->mWarehouseFrom->name;
            },
            /** どこへ */
            function ($inventory_data) {
                return $inventory_data->mWarehouseTo->name;
            },
            /** 商品 */
            function ($inventory_data) {
                return $inventory_data->product_name;
            },
            /** 数量 */
            function ($inventory_data) {
                return $inventory_data->quantity;
            },
            /** 備考 */
            function ($inventory_data) {
                return $inventory_data->note;
            },
        ];

        if ($inventory_data->isEmpty()) {
            // 在庫データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('inventory.inventory_datas.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $inventory_data->exportExcel($filename, $headings, $filters);
    }
}
