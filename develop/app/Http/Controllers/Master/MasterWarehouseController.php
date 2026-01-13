<?php

/**
 * 倉庫マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Enums\IsControlInventory;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\WarehouseEditRequest;
use App\Http\Requests\Master\WarehouseSearchRequest;
use App\Models\Master\MasterWarehouse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 倉庫マスター画面用コントローラー
 */
class MasterWarehouseController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterWarehouseController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param WarehouseSearchRequest $request
     * @return View
     */
    public function index(WarehouseSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'warehouses' => MasterWarehouse::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.warehouses.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param WarehouseSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(WarehouseSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.warehouses');
        $headings = [
            'コード',
            '倉庫名',
            '倉庫名かな',
            '在庫管理',
            '作成日時',
            '更新日時',
        ];

        $warehouses = MasterWarehouse::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($warehouses) {
                return $warehouses->code_zerofill;
            },
            /** 倉庫名 */
            function ($warehouses) {
                return $warehouses->name;
            },
            /** 倉庫名かな */
            function ($warehouses) {
                return $warehouses->name_kana;
            },
            /** 在庫管理 */
            function ($warehouses) {
                return IsControlInventory::getDescription($warehouses->is_control_inventory);
            },
            /** 作成日時 */
            function ($warehouses) {
                return Carbon::parse($warehouses->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($warehouses) {
                return Carbon::parse($warehouses->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($warehouses->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.warehouses.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $warehouses->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterWarehouse();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/warehouses*', $this->refURLMasterKey());

        return view('master.warehouses.create_edit', $this->sendDataWarehouse($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param WarehouseEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(WarehouseEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $warehouse = new MasterWarehouse();

        try {
            $warehouse->code = $request->code;
            $warehouse->name = $request->name;
            $warehouse->name_kana = $request->name_kana;
            $warehouse->is_control_inventory = $request->is_control_inventory;

            $warehouse->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $warehouse->code_zero_fill, $warehouse->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.warehouses.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterWarehouse $warehouse
     * @return View
     */
    public function edit(MasterWarehouse $warehouse): View
    {
        return view('master.warehouses.create_edit', $this->sendDataWarehouse($warehouse));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param WarehouseEditRequest $request
     * @param MasterWarehouse $warehouse
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(WarehouseEditRequest $request, MasterWarehouse $warehouse): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $warehouse->code = $request->code;
            $warehouse->name = $request->name;
            $warehouse->name_kana = $request->name_kana;
            $warehouse->is_control_inventory = $request->is_control_inventory;

            $warehouse->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $warehouse->code_zero_fill, $warehouse->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterWarehouse $warehouse
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterWarehouse $warehouse): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($warehouse->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $warehouse->code_zero_fill, $warehouse->name);

            return redirect(route('master.warehouses.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $warehouse->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $warehouse->code_zero_fill, $warehouse->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.warehouses.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
