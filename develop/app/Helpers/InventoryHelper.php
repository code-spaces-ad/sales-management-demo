<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\InventoryType;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataClosing;
use App\Models\Inventory\InventoryStockData;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 在庫調整画面表示用ヘルパークラス
 */
class InventoryHelper
{
    /**
     * Store a newly created resource in storage.
     *
     * @param array $orders_received
     * @param bool $isCopy
     * @return void
     *
     * @throws Exception
     */
    public static function inventoryStore(array $orders_received, bool $isCopy = false): array
    {
        $inventory_data = $inventory_data_closing = [];

        DB::beginTransaction();

        try {
            // 在庫データ詳細を登録
            foreach ($orders_received as $key => $detail) {
                if (!$detail['delivery_date']) {
                    continue;
                }

                // 複製時
                if ($isCopy) {
                    $detail['quantity'] = 0;
                }

                // 在庫データを登録
                $inventory_data[] = self::registInventoryData($detail, InventoryType::INVENTORY_OUT, $key);

                // 納品日が過去月か判定
                if (DateHelper::isLessThanThisMonth($detail['delivery_date'])) {
                    // 締在庫数へ登録
                    $inventory_data_closing[] = self::registInventoryDataClosing($detail);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        return [$inventory_data, $inventory_data_closing];
    }

    /**
     * update a newly created resource in storage.
     *
     * @param array $orders_received
     * @param array $before_orders_received
     * @return void
     *
     * @throws Exception
     */
    public static function inventoryUpdate(array $orders_received, array $before_orders_received): array
    {
        $inventory_data = $inventory_data_closing = [];

        DB::beginTransaction();

        try {
            // 在庫データ詳細を登録
            foreach ($orders_received as $key => $detail) {
                // 在庫データに登録するデータか判定
                if (isset($before_orders_received[$key]) && self::checkSameData($detail, $before_orders_received[$key])) {
                    continue;
                }
                // 納品日が未入力の場合、continue
                if (is_null($detail['delivery_date'])) {
                    continue;
                }

                // 在庫データへ登録
                $inventory_data[] = self::registInventoryData($detail, InventoryType::INVENTORY_OUT, $key);

                // 更新前にdelivery_dateが入力されていた且つ納品日が過去月か判定
                if (isset($before_orders_received[$key]['delivery_date']) &&
                    DateHelper::isLessThanThisMonth($before_orders_received[$key]['delivery_date'])) {
                    $before_orders_received[$key]['quantity'] = -$before_orders_received[$key]['quantity'];
                    // 締在庫数へ登録(一旦、在庫プラス処理)
                    $inventory_data_closing[] = self::registInventoryDataClosing($before_orders_received[$key]);
                }
                // 納品日が過去月か判定
                if (DateHelper::isLessThanThisMonth($detail['delivery_date'])) {
                    // 締在庫数へ登録
                    $inventory_data_closing[] = self::registInventoryDataClosing($detail);
                }
            }

            // 在庫データを削除
            foreach ($before_orders_received as $key => $detail) {
                // 更新後の受注伝票に無い在庫データを削除
                if (!isset($orders_received[$key])) {
                    self::deleteInventoryData($detail);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        return [$inventory_data, $inventory_data_closing];
    }

    /**
     * 数量調整処理
     *
     * @param array $detailData
     * @return Model|null
     */
    public static function returnInventory(array $detailData): ?Model
    {
        $builder = InventoryStockData::query()
            ->where('warehouse_id', $detailData['warehouse_id'])
            ->where('product_id', $detailData['product_id']);

        if (!$builder->exists()) {
            return null;
        }

        $stock_data = $builder->get();

        foreach ($stock_data as $stocks_data) {
            $stocks = $stocks_data->inventory_stocks;

            /** 現在庫数 + 数量 */
            $stocks = $stocks + $detailData['quantity'];

            $inventory_stock_data = new InventoryStockData();

            $inventory_stock_data->query()
                ->updateOrInsert(
                    [
                        /** 倉庫ID */
                        'warehouse_id' => $detailData['warehouse_id'],
                        /** 商品ID */
                        'product_id' => $detailData['product_id'],
                    ],
                    [
                        /** 新在庫数 */
                        'inventory_stocks' => $stocks,
                        /** 更新者ID */
                        'updated_id' => Auth::user()->id,
                    ]
                );
        }

        return (new InventoryStockData())->query()
            ->where('warehouse_id', $detailData['warehouse_id'])
            ->where('product_id', $detailData['product_id'])
            ->first();
    }

    /**
     * データ一致チェック
     *
     * @param array $detail
     * @param array $before_data
     * @return bool
     */
    public static function checkSameData(array $detail, array $before_data): bool
    {
        // 商品名
        if ($detail['product_id'] !== $before_data['product_id']) {
            return false;
        }
        // 納品日
        if ($detail['delivery_date'] !== $before_data['delivery_date']) {
            return false;
        }
        // 倉庫
        if ($detail['warehouse_id'] !== $before_data['warehouse_id']) {
            return false;
        }
        // 数量
        if ($detail['quantity'] !== $before_data['quantity']) {
            return false;
        }

        return true;
    }

    /**
     * 現在庫データへ登録
     *
     * @param array $details
     * @return array
     */
    public static function setDataForUpdatingAndUpdated(array $details): array
    {
        $updating_inventory_stock_data = $updated_inventory_stock_data = [];

        // 現在庫データ作成
        foreach ($details as $detail) {
            // 納品日が未入力の場合、continue
            if (!$detail['delivery_date']) {
                continue;
            }

            // 現在庫データを更新し、更新後のデータとして取得
            $updated_inventory_stock_data[] = self::updateInventoryStockData($detail);
        }

        return [$updating_inventory_stock_data, $updated_inventory_stock_data];
    }

    /**
     * 現在庫データへ登録
     *
     * @param array $detail
     * @return Model
     */
    public static function updateInventoryStockData(array $detail): Model
    {
        // 在庫マイナス処理の為、数量のマイナス値セット
        $detail['inventory_stocks'] = -$detail['quantity'];
        $detail['updated_id'] = Auth::user()->id;

        $inventory_stock_data = (new InventoryStockData())->query()
            ->firstOrNew((new InventoryStockData($detail))->toArray());

        $builder = $inventory_stock_data->query()
            ->where('warehouse_id', $detail['warehouse_id'])
            ->where('product_id', $detail['product_id']);

        if ($builder->exists()) {
            /** 現在庫数 - 数量 */
            $detail['inventory_stocks'] += $builder->value('inventory_stocks');
            $inventory_stock_data = $builder->first();
        }

        $inventory_stock_data->query()
            ->updateOrInsert(
                [
                    /** 倉庫ID */
                    'warehouse_id' => $detail['warehouse_id'],
                    /** 商品ID */
                    'product_id' => $detail['product_id'],
                ],
                [
                    /** 新在庫数 */
                    'inventory_stocks' => $detail['inventory_stocks'],
                    /** 更新者ID */
                    'updated_id' => $detail['updated_id'],
                ]
            );

        return $inventory_stock_data
            ->where('warehouse_id', $detail['warehouse_id'])
            ->where('product_id', $detail['product_id'])
            ->first();
    }

    /**
     * 在庫データへ登録
     *
     * @param array $detail
     * @param int $to_warehouse_id
     * @param int $key
     * @return Model
     */
    public static function registInventoryData(array $detail, int $to_warehouse_id, int $key): Model
    {
        // 在庫データ登録
        $inventory_data = InventoryData::query()
            ->updateOrCreate(
                [
                    'orders_received_number' => $detail['orders_received_id'],
                    'orders_received_details_sort' => $detail['sort'],
                ],
                [
                    'inout_date' => $detail['delivery_date'] ?? now()->format('Y/m/d'),
                    'inout_status' => 1,
                    'from_warehouse_id' => $detail['warehouse_id'],
                    'to_warehouse_id' => $to_warehouse_id,
                    'employee_id' => Auth::user()->employee_id,
                    'note' => $detail['note'],
                    'updated_id' => Auth::user()->id,
                ]
            );

        // 在庫データ詳細登録
        $inventory_data->InventoryDataDetail()
            ->updateOrInsert(
                [
                    'inventory_data_id' => $inventory_data['id'],
                    'sort' => $key,
                ],
                [
                    'product_id' => $detail['product_id'],
                    'product_name' => $detail['product_name'],
                    'quantity' => $detail['quantity'],
                    'note' => $detail['note'],
                ]
            );

        return $inventory_data;
    }

    /**
     * 締在庫数へ登録
     *
     * @param array $detail
     * @return Model|null
     */
    public static function registInventoryDataClosing(array $detail): ?Model
    {
        $closing_ym = DateHelper::changeDateFormat($detail['delivery_date'], 'Ym');

        // 更新する納品日付(月)と今月を比較し、納品日付が過去月でなければ、return
        if (!DateHelper::isLessThanThisMonth($detail['delivery_date'])) {
            return null;
        }

        // 在庫マイナス処理の為、数量のマイナス値セット
        $detail['inventory_stocks'] = -$detail['quantity'];

        // 検索条件のBuilderをセット
        $builder = (new InventoryDataClosing())->query()
            ->where('warehouse_id', $detail['warehouse_id'])
            ->where('product_id', $detail['product_id'])
            ->where('closing_ym', $closing_ym);

        // 既に締在庫数が存在していたら、締在庫数を数量でマイナス
        if ($builder->exists()) {
            $detail['inventory_stocks'] = $builder->value('closing_stocks') - $detail['quantity'];
        }

        // 締在庫数へ登録
        return InventoryDataClosingHelper::updateOrInsertInventoryDataClosing($detail, $closing_ym);
    }

    /**
     * 対象の在庫データを削除
     *
     * @param array $detail
     * @return Model|null
     *
     * @throws Exception
     */
    public static function deleteInventoryData(array $detail): ?Model
    {
        $inventory_data = InventoryData::query()
            ->where('orders_received_number', $detail['orders_received_id'])
            ->where('orders_received_details_sort', $detail['sort'])
            ->first();

        if (!$inventory_data) {
            return null;
        }

        // 在庫データ削除
        $inventory_data->InventoryDataDetail()->delete();
        $inventory_data->delete();

        return $inventory_data;
    }
}
