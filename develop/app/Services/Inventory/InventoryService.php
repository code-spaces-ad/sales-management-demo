<?php

/**
 * 在庫処理用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Inventory;

use App\Consts\SessionConst;
use App\Enums\StorehouseStatus;
use App\Helpers\InventoryHelper;
use App\Helpers\LogHelper;
use App\Http\Requests\Inventory\InventoryEditRequest;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataStatusHistory;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterWarehouse;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryStockDataRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 在庫処理用サービス
 */
class InventoryService
{
    use SessionConst;

    protected InventoryRepository $repository;

    protected InventoryStockDataRepository $inventory_stock_data;

    /**
     * Repositoryをインスタンス
     *
     * @param InventoryRepository $repository
     * @param InventoryStockDataRepository $inventory_stock_data
     */
    public function __construct(InventoryRepository $repository,
        InventoryStockDataRepository $inventory_stock_data
    ) {
        $this->repository = $repository;
        $this->inventory_stock_data = $inventory_stock_data;
    }

    /**
     * 一覧画面
     *
     * @param array $input_data
     * @return array
     */
    public function index(array $input_data): array
    {
        return [
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
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'inventory_datas' => $this->repository->getSearchResult($input_data),
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param InventoryData $target_data
     * @return array
     */
    public function create(InventoryData $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 倉庫マスター */
                'warehouses' => MasterWarehouse::query()->oldest('code')->get(),
                /** 商品データ */
                'products' => MasterProduct::getProductData(),
                /** 現在庫データ */
                'inventory_stock_data' => InventoryStockData::query()->get(),
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
                /** 状態 */
                'Storehouse_Status' => StorehouseStatus::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** 在庫処理使用セッションキー(URL) */
            'session_inventory_key' => $this->refURLInventoryKey(),
        ];
    }

    /**
     * 新規登録処理
     *
     * @param InventoryEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(InventoryEditRequest $request): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'inout_status' => 1,
                'note' => null,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 在庫データを登録
            $inventory_data = $this->repository->createInventoryData($request->input());

            // 在庫データ詳細を登録
            $sort_index = 1;
            $details = [];
            foreach ($request->detail ?? [] as $detail) {
                // 在庫データ詳細の登録内容セット
                $details[] = [
                    'inventory_data_id' => $inventory_data->id,
                    'product_id' => $detail['product_id'],
                    'product_name' => $detail['product_name'],
                    'quantity' => $detail['quantity'] ?? 0,
                    'sort' => $sort_index,
                    'note' => $detail['note'] ?? null,
                ];

                // 締在庫数へ登録する為、inout_dateをdelivery_dateにセット
                $detail['delivery_date'] = $inventory_data->inout_date;
                // 締在庫数を登録 from_warehouse_idの処理
                $detail['warehouse_id'] = $inventory_data->from_warehouse_id;
                InventoryHelper::registInventoryDataClosing($detail);
                // 締在庫数を登録 to_warehouse_idの処理 ※在庫が増える為、データを渡す前にマイナス値をセット
                $detail['quantity'] = -$detail['quantity'];
                $detail['warehouse_id'] = $inventory_data->to_warehouse_id;
                InventoryHelper::registInventoryDataClosing($detail);

                ++$sort_index;
            }
            // 在庫データ詳細を登録
            $this->repository->createInventoryDataDetails($details);

            // 現在庫データ更新
            foreach ($request->detail ?? [] as $detail) {
                // 在庫管理しない倉庫IDリスト
                $do_not_control_inventory_list = MasterWarehouse::getDoNotControlInventoryList();
                // From倉庫が在庫管理しない倉庫IDか判定
                $from_do_not_control = in_array($inventory_data->from_warehouse_id, $do_not_control_inventory_list);
                // To倉庫が在庫管理しない倉庫IDか判定
                $to_do_not_control = in_array($inventory_data->to_warehouse_id, $do_not_control_inventory_list);

                // FromもToも在庫管理しない倉庫だった場合、continue
                if ($from_do_not_control && $to_do_not_control) {
                    continue;
                }

                $detail['from_warehouse_id'] = $inventory_data->from_warehouse_id;
                $detail['to_warehouse_id'] = $inventory_data->to_warehouse_id;
                $detail['updated_id'] = Auth::user()->id;
                // 現在庫データ更新
                self::updateStockDataFromAndTo($from_do_not_control, $to_do_not_control, $detail);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = config('consts.message.common.store_success');
        if ($error_flag) {
            $message = config('consts.message.common.store_failed');
        }

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param InventoryData $target_data
     * @return array
     */
    public function edit(InventoryData $target_data): array
    {
        $add_data = [
            /** 入出庫状態履歴 */
            'status_history' => InventoryDataStatusHistory::query()
                ->where('inventory_data_id', $target_data->id)
                ->orderByDesc('created_at')
                ->get(),
        ];

        return array_merge_recursive($this->create($target_data), $add_data);
    }

    /**
     * 更新処理
     *
     * @param InventoryEditRequest $request
     * @param InventoryData $target_data
     * @return array
     *
     * @throws Exception
     */
    public function update(InventoryEditRequest $request, InventoryData $target_data): array
    {
        $error_flag = false;

        $message = config('consts.message.common.update_success');
        if ($error_flag) {
            $message = config('consts.message.common.update_failed');
        }

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param InventoryData $target_data
     * @return array
     *
     * @throws Exception
     */
    public function destroy(InventoryData $target_data): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 在庫データ削除
            $this->repository->deleteInventoryData($target_data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        $message = config('consts.message.common.destroy_success');
        if ($error_flag) {
            $message = config('consts.message.common.destroy_failed');
        }

        return [$error_flag, $message];
    }

    /**
     * From倉庫とTo倉庫の現在庫データ更新
     *
     * @param bool $from_do_not
     * @param bool $to_do_not
     * @param array $detail
     * @return void
     */
    public function updateStockDataFromAndTo(bool $from_do_not, bool $to_do_not, array $detail): void
    {
        // From倉庫が在庫管理する倉庫IDだった場合、現在庫データ更新 (マイナス処理)
        self::updateInventoryStockData($from_do_not, $detail, $detail['from_warehouse_id'], -$detail['quantity']);
        // To倉庫が在庫管理する倉庫IDだった場合、現在庫データ更新 (プラス処理)
        self::updateInventoryStockData($to_do_not, $detail, $detail['to_warehouse_id'], $detail['quantity']);
    }

    /**
     * 現在庫データ更新
     *
     * @param bool $do_not
     * @param array $detail
     * @param int $warehouse_id
     * @param int $quantity
     * @return void
     */
    public function updateInventoryStockData(bool $do_not, array $detail, int $warehouse_id, int $quantity): void
    {
        // 在庫管理しない場合、return
        if ($do_not) {
            return;
        }

        // 検索条件のBuilderをセット
        $builder = InventoryStockData::query()
            ->where('warehouse_id', $warehouse_id)
            ->where('product_id', $detail['product_id']);

        $detail['warehouse_id'] = $warehouse_id;
        $detail['inventory_stocks'] = $quantity;
        // 現在庫データが存在する場合、現在庫数 + 数量
        if ($builder->exists()) {
            $detail['inventory_stocks'] += $builder->value('inventory_stocks');
        }
        // 現在庫データ更新
        $this->inventory_stock_data
            ->updateInventoryStockData((new InventoryStockData())->fill($detail)->toArray());
    }
}
