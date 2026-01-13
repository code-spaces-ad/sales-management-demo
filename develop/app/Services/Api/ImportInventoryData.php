<?php

namespace App\Services\Api;

use App\Consts\API\PosApiConst;
use App\Enums\InventoryType;
use App\Enums\SalesClassification;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataDetail;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterSupplierProduct;
use App\Models\Master\MasterWarehouse;
use App\Models\Sale\SalesOrder;
use App\Models\Trading\PurchaseOrder;
use App\Repositories\Inventory\InventoryRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class ImportInventoryData
{
    /** 取込スキップフラグ */
    private bool $is_skip_import;

    public function __construct() {}

    /**
     * 売上⇒出庫処理
     *
     * @param SalesOrder $sales_order
     * @param bool $delete_flg
     * @return void
     *
     * @throws Exception
     */
    public function setSalesOrder(SalesOrder $sales_order, bool $delete_flg = false)
    {
        try {
            // 現在庫データ出庫時のマイナス反転(取消・返品の場合は入庫扱い)
            $reverse_sign = -1;
            if ($sales_order->sales_classification_id == SalesClassification::CLASSIFICATION_RETURN) {
                $reverse_sign = 1;
            }
            if ($delete_flg) {
                $reverse_sign = 1;
            }

            // 事業所コードから倉庫を取得する
            $office_facilities = MasterOfficeFacility::where('id', $sales_order->office_facilities_id)->first();
            $warehouse = MasterWarehouse::where('code', $office_facilities['code'])->first();

            // 在庫データ登録
            $insert_data = [
                'orders_received_number' => $sales_order->order_number, // 受注番号
                'inout_date' => $sales_order->order_date,   // 入出庫日付
                'inout_status' => 1,    // 状態
                'from_warehouse_id' => $warehouse->id,  // 移動元倉庫ID
                'to_warehouse_id' => InventoryType::INVENTORY_OUT,  // 移動先倉庫ID
                'employee_id' => PosApiConst::POS_DATA_CREATER,
                'updated_id' => PosApiConst::POS_DATA_CREATER,
                'created_at' => $sales_order->created_at,
                'updated_at' => $sales_order->updated_at,
            ];
            if ($sales_order->sales_classification_id == SalesClassification::CLASSIFICATION_RETURN || $delete_flg) {
                // 取消・返品の場合は入庫扱い
                $insert_data['from_warehouse_id'] = InventoryType::INVENTORY_IN;
                $insert_data['to_warehouse_id'] = $warehouse->id;
            }
            $inventory_data = InventoryData::query()->create($insert_data);

            // 詳細データ取得
            $sales_order_details = $sales_order->salesOrderDetail;
            foreach ($sales_order_details as $detail) {
                // 在庫詳細データの登録
                $insert_data = [
                    'inventory_data_id' => $inventory_data->id, // 在庫データID
                    'product_id' => $detail['product_id'],  // 商品ID
                    'product_name' => $detail['product_name'],  // 商品名
                    // 入出庫時の数量はマイナス付にしない
                    'quantity' => $detail['quantity'],  // 数量
                    'sort' => $detail['sort'],  // ソート
                ];
                InventoryDataDetail::query()->create($insert_data);

                // 現在庫データの登録
                $inventory_stock_data = InventoryStockData::where('warehouse_id', $sales_order->office_facilities_id)
                    ->where('product_id', $detail['product_id'])
                    ->first();
                if (empty($inventory_stock_data)) {
                    // 新規登録
                    $insert_data = [
                        'warehouse_id' => $sales_order->office_facilities_id,    // 移動先倉庫ID
                        'product_id' => $detail['product_id'],  // 商品ID
                        // 出庫時はマイナス数量
                        'inventory_stocks' => $reverse_sign * $detail['quantity'],  // 現在庫数
                        'updated_id' => PosApiConst::POS_DATA_CREATER,    // 更新者ID
                        'created_at' => $sales_order->created_at,
                        'updated_at' => $sales_order->updated_at,
                    ];
                    InventoryStockData::query()->create($insert_data);
                }
                if (!empty($inventory_stock_data)) {
                    // 更新処理
                    $inventory_stock_data->update([
                        'inventory_stocks' => $inventory_stock_data->inventory_stocks - $detail['quantity'],
                        'updated_id' => PosApiConst::POS_DATA_CREATER,    // 更新者ID
                        'created_at' => $sales_order->created_at,
                        'updated_at' => $sales_order->updated_at,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::channel('pos_err')->error('【Error】ImportInventoryData | setPurchaseOrder :　' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 入出庫処理
     *
     * @param array $order_data
     * @return bool
     *
     * @throws Exception
     */
    public function createInventoryData(array $order_data): bool
    {
        $this->is_skip_import = false;
        $info_msg = 'POS連携 販売データ：取込スキップ 伝票番号=' . $order_data['order_id']
            . ', 店舗コード=' . $order_data['store_no'] . ', 更新日時=' . $order_data['update_date'];

        $reverse_sign = -1;
        if (intval($order_data['torihiki_status']) === 6) {
            // 6：入庫の場合
            $reverse_sign = 1;
        }

        try {
            // 事業所コードから倉庫を取得する
            $warehouse = MasterWarehouse::where('code', $order_data['store_no'])->first();
            // 在庫データの既存データ確認
            $inventory_order_data = InventoryData::query()
                ->where('orders_received_number', $order_data['order_id'])
                ->where('inout_date', $order_data['sales_date'])
                ->first();
            if (!empty($inventory_order_data)) {
                Log::channel('pos_info')->info($info_msg . ', 既存の伝票');

                return $this->is_skip_import;
            }

            // 在庫データ登録
            $insert_data = [
                'orders_received_number' => $order_data['order_id'], // 受注番号
                'inout_date' => $order_data['sales_date'],   // 入出庫日付
                'inout_status' => 1,    // 状態
                'from_warehouse_id' => $warehouse['id'],  // 移動元倉庫ID
                'to_warehouse_id' => InventoryType::INVENTORY_OUT,  // 移動先倉庫ID
                'employee_id' => PosApiConst::POS_DATA_CREATER,
                'updated_id' => PosApiConst::POS_DATA_CREATER,
                'created_at' => $order_data['create_date'],
                'updated_at' => $order_data['update_date'],
            ];
            if ($order_data['torihiki_status'] == SalesClassification::CLASSIFICATION_RETURN
                || $order_data['torihiki_status'] == 6) {
                // 返品・入庫の場合は入庫扱い
                $insert_data['from_warehouse_id'] = InventoryType::INVENTORY_IN;
                $insert_data['to_warehouse_id'] = $warehouse['id'];
            }
            $inventory_data = InventoryData::query()->create($insert_data);

            $del_flg = false;
            // 詳細データ取得
            $order_details = $order_data['details'];
            foreach ($order_details as $key => $detail) {
                $m_products = MasterProduct::where('code', $detail->product_code)->first();
                if (empty($m_products)) {
                    // 取込スキップフラグ
                    Log::channel('pos_info')->info($info_msg . ', 存在しない商品コード=' . $detail->product_code);
                    $del_flg = true;
                    $this->is_skip_import = true;

                    continue;
                }

                // 在庫詳細データの登録
                $insert_data = [
                    'inventory_data_id' => $inventory_data->id, // 在庫データID
                    'product_id' => $m_products['id'],  // 商品ID
                    'product_name' => $m_products['name'],  // 商品名
                    // 入出庫時の数量はマイナス付にしない
                    'quantity' => $detail->quantity,  // 数量
                    'sort' => $key,  // ソート
                ];
                InventoryDataDetail::query()->create($insert_data);
            }

            if (!$del_flg) {
                // 現在庫データの登録
                self::setInventoryStockData($inventory_data->id, $warehouse['id'], $reverse_sign);
            }
            if ($del_flg) {
                // 在庫データの削除
                self::deleteInventoryData($inventory_data->id);
            }

            return $this->is_skip_import;

        } catch (Exception $e) {
            Log::channel('pos_err')->error('【Error】' . __CLASS__ . ' | ' . __FUNCTION__ . ' :　' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 仕入⇒入庫処理
     *
     * @param PurchaseOrder $purchase_order
     * @return void
     *
     * @throws Exception
     */
    public function setPurchaseOrder(PurchaseOrder $purchase_order)
    {
        try {
            // 在庫データ登録
            $insert_data = [
                // 受注番号
                'orders_received_number' => $purchase_order->order_number,
                // 入出庫日付
                'inout_date' => $purchase_order->created_at,
                // 状態
                'inout_status' => 1,
                // 移動元倉庫ID
                'from_warehouse_id' => InventoryType::INVENTORY_IN,
                // 移動先倉庫ID
                'to_warehouse_id' => $purchase_order->office_facilities_id,
                // 担当者ID
                'employee_id' => PosApiConst::POS_DATA_CREATER,
                // 更新者ID
                'updated_id' => $purchase_order->updated_id,
            ];

            $reverse_sign = 1;
            // ※売上側でサービス・試飲・その他の処理を行うためコメントアウト
            //            if ($purchase_order->purchase_classification_id == PurchaseClassification::CLASSIFICATION_SERVICE
            //                || $purchase_order->purchase_classification_id == PurchaseClassification::CLASSIFICATION_TASTING
            //                || $purchase_order->purchase_classification_id == PurchaseClassification::CLASSIFICATION_OTHER) {
            //                // サービス・試飲・その他の場合は出庫扱い
            //                $insert_data['from_warehouse_id'] = $purchase_order->office_facilities_id;
            //                $insert_data['to_warehouse_id'] = InventoryType::INVENTORY_OUT;
            //                // 現在庫データ出庫時のマイナス反転
            //                $reverse_sign = -1;
            //            }

            $inventory_data = InventoryData::query()->create($insert_data);

            // 詳細データ取得
            $purchase_order_details = $purchase_order->purchaseOrderDetail;
            foreach ($purchase_order_details as $detail) {
                // 商品マスタの取得
                $last_unit_price = MasterProduct::find($detail['product_id'])->unit_price ?? 0;
                if ($last_unit_price != $detail['unit_price']) {
                    // 仕入先_商品リレーションの取得
                    $m_supplier_product = MasterSupplierProduct::where('supplier_id', $purchase_order->supplier_id)
                        ->where('product_id', $detail['product_id'])
                        ->first();

                    // 仕入先_商品リレーションの登録
                    if (empty($m_supplier_product)) {
                        // 新規登録
                        $insert_data = [
                            // 仕入先ID
                            'supplier_id' => $purchase_order->supplier_id,
                            // 商品ID
                            'product_id' => $detail['product_id'],
                            // 単位
                            'unit_name' => $detail['unit_name'],
                            // 最終単価
                            'last_unit_price' => $detail['unit_price'],
                            // 作成日時
                            'created_at' => $purchase_order->created_at,
                            // 更新日時
                            'updated_at' => $purchase_order->created_at,
                        ];
                        MasterSupplierProduct::query()->create($insert_data);
                    }
                    if (!empty($m_supplier_product) && $purchase_order->created_at > $m_supplier_product->updated_at) {
                        // 更新処理
                        $m_supplier_product->update([
                            'last_unit_price' => $detail['product_id'],
                            'updated_at' => $purchase_order->created_at,
                        ]);
                    }
                }

                // 在庫詳細データの登録
                $insert_data = [
                    // 在庫データID
                    'inventory_data_id' => $inventory_data->id,
                    // 商品ID
                    'product_id' => $detail['product_id'],
                    // 商品名
                    'product_name' => $detail['product_name'],
                    // 数量
                    'quantity' => $detail['quantity'],
                    // ソート
                    'sort' => $detail['sort'],
                ];
                InventoryDataDetail::query()->create($insert_data);

                // 現在庫データの登録
                $inventory_stock_data = InventoryStockData::where('warehouse_id', $purchase_order->office_facilities_id)
                    ->where('product_id', $detail['product_id'])
                    ->first();
                if (empty($inventory_stock_data)) {
                    // 新規登録
                    $insert_data = [
                        // 移動先倉庫ID
                        'warehouse_id' => $purchase_order->office_facilities_id,
                        // 商品ID
                        'product_id' => $detail['product_id'],
                        // 現在庫数 ※出庫時は反転
                        'inventory_stocks' => $reverse_sign * $detail['quantity'],
                        // 更新者ID
                        'updated_id' => $purchase_order->updated_id,
                    ];
                    InventoryStockData::query()->create($insert_data);
                }
            }
        } catch (Exception $e) {
            Log::channel('pos_err')->error('【Error】ImportInventoryData | setPurchaseOrder :　' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 入庫処理(仕入データ受信)
     *
     * @param array $received_data
     * @return void
     *
     * @throws Exception
     */
    public function setInventoryReceipt(array $received_data): bool
    {
        $this->is_skip_import = false;
        $info_msg = 'POS連携 仕入データ：取込スキップ 伝票番号=' . $received_data['stock_change_id']
            . ', 店舗コード=' . $received_data['store_no'] . ', 更新日時=' . $received_data['update_date'];

        try {
            // 伝票番号
            $order_number = $received_data['stock_change_id'];

            // 仕入年月日
            $purchase_date = new Carbon(
                substr($received_data['purchase_date'], 0, 4)
                . '-' . substr($received_data['purchase_date'], 4, 2)
                . '-' . substr($received_data['purchase_date'], 6, 2)
            );

            // 倉庫情報取得
            $warehouse = MasterWarehouse::query()->where('code', $received_data['store_no'])->first();
            if (empty($warehouse)) {
                // 存在しない倉庫
                Log::channel('pos_info')->info($info_msg . ', 存在しない倉庫コード=' . $received_data['store_no']);

                return $this->is_skip_import = true;
            }

            $inventory_data = InventoryData::query()->where('orders_received_number', $order_number)
                ->where('inout_date', $purchase_date)
                ->where('to_warehouse_id', $warehouse['id'])
                ->first();
            if (!empty($inventory_data)) {
                Log::channel('pos_info')->info($info_msg . ', 既存の伝票');

                return $this->is_skip_import;
            }

            // 在庫データ登録
            $insert_data = [
                // 受注番号
                'orders_received_number' => $order_number,
                // 入出庫日付
                'inout_date' => $purchase_date,
                // 状態
                'inout_status' => 1,
                // 移動元倉庫ID
                'from_warehouse_id' => InventoryType::INVENTORY_IN,     // 仕入
                // 移動先倉庫ID
                'to_warehouse_id' => $warehouse['id'],
                // 担当者ID
                'employee_id' => PosApiConst::POS_DATA_CREATER,
                // 更新者ID
                'updated_id' => PosApiConst::POS_DATA_CREATER,
                'created_at' => $received_data['create_date'],
                'updated_at' => $received_data['update_date'],
            ];

            InventoryData::query()->insert($insert_data);
            $inventory_data = InventoryData::query()->where('orders_received_number', $order_number)
                ->where('inout_date', $purchase_date)
                ->where('to_warehouse_id', $warehouse['id'])
                ->first();

            // 在庫詳細データの登録
            $msg_not_exist_product = null;
            $sort_index = 1;
            foreach ($received_data['PURCHASE_DETAIL_DATA'] ?? [] as $detail) {
                // 商品マスタ取得
                $product = MasterProduct::query()
                    ->where('code', $detail->product_code)
                    ->first();
                if (empty($product)) {
                    $msg_not_exist_product = ', 存在しない商品コード=' . $detail->product_code
                        . ', 商品名=' . $detail->product_name;
                    // 存在しない商品の場合はデータ登録しない
                    Log::channel('pos_info')->info($info_msg . $msg_not_exist_product);
                    $this->is_skip_import = true;

                    continue;
                }

                $insert_data = [
                    // 在庫データID
                    'inventory_data_id' => $inventory_data->id,
                    // 商品ID
                    'product_id' => $product['id'],
                    // 商品名
                    'product_name' => $product['name'],
                    // 数量
                    'quantity' => $detail->quantity,
                    // ソート
                    'sort' => $sort_index,
                    'created_at' => $received_data['create_date'],
                    'updated_at' => $received_data['update_date'],
                ];
                InventoryDataDetail::query()->insert($insert_data);

                ++$sort_index;
            }

            if (is_null($msg_not_exist_product)) {
                self::setInventoryStockData($inventory_data->id, $warehouse['id']);
            }

            if (!is_null($msg_not_exist_product)) {
                self::deleteInventoryData($inventory_data->id);
            }

            return $this->is_skip_import;

        } catch (Exception $e) {
            Log::channel('pos_err')->error('【Error】ImportInventoryData | setInventoryReceipt :　' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 現在庫データの登録
     *
     * @param int $inventory_data_id
     * @param int $warehouse_id
     * @return void
     *
     * @throws Exception
     */
    public function setInventoryStockData(int $inventory_data_id, int $warehouse_id, int $reverse_sign = 1): void
    {
        try {
            $inventory_data_detail = InventoryDataDetail::query()
                ->where('inventory_data_id', $inventory_data_id)
                ->get();

            foreach ($inventory_data_detail as $detail) {
                // 現在庫データ取得
                $inventory_stock_data = InventoryStockData::query()
                    ->where('warehouse_id', $warehouse_id)
                    ->where('product_id', $detail->product_id)
                    ->first();

                if (empty($inventory_stock_data)) {
                    // 新規登録
                    $insert_data = [
                        // 移動先倉庫ID
                        'warehouse_id' => $warehouse_id,
                        // 商品ID
                        'product_id' => $detail->product_id,
                        // 現在庫数
                        'inventory_stocks' => $reverse_sign * $detail->quantity,
                        // 更新者ID
                        'updated_id' => PosApiConst::POS_DATA_CREATER,
                        'created_at' => $detail->created_at,
                        'updated_at' => $detail->updated_at,
                    ];
                    InventoryStockData::query()->insert($insert_data);
                }

                if (!empty($inventory_stock_data)) {
                    // 更新処理
                    InventoryStockData::query()
                        ->where('id', $inventory_stock_data->id)
                        ->update([
                            'inventory_stocks' => $inventory_stock_data->inventory_stocks + $reverse_sign * $detail->quantity,
                            'updated_at' => $detail->updated_at,
                        ]);
                }
            }
        } catch (Exception $e) {
            Log::channel('pos_err')->error('【Error】ImportInventoryData | setInventoryStockData :　' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 在庫データ削除
     *
     * @param int $inventory_data_id
     * @return void
     *
     * @throws Exception
     */
    public function deleteInventoryData(int $inventory_data_id): void
    {
        try {
            $inventory_order = InventoryData::query()
                ->where('id', $inventory_data_id)
                ->first();
            $order_repository = new InventoryRepository(new InventoryData());
            $inventory_order = $order_repository->deleteInventoryData($inventory_order);
        } catch (Exception $e) {
            Log::channel('pos_err')->error('【Error】ImportInventoryData | deleteInventoryData :　' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 在庫調整 処理(棚卸データ)
     *
     * @param array $received_data
     * @return void
     *
     * @throws Exception
     */
    public function setInventoryAdjustment(array $received_data): bool
    {
        $this->is_skip_import = false;
        $info_msg = 'POS連携 棚卸：取込スキップ 棚卸日付=' . $received_data['inventory_date']
            . ', 店舗コード=' . $received_data['store_no'];

        try {
            // 棚卸日付
            $inventory_date = new Carbon(
                substr($received_data['inventory_date'], 0, 4)
                . '-' . substr($received_data['inventory_date'], 4, 2)
                . '-' . substr($received_data['inventory_date'], 6, 2)
            );

            // 倉庫情報取得
            $warehouse = MasterWarehouse::where('code', $received_data['store_no'])->first();
            if (empty($warehouse)) {
                // 存在しない倉庫
                Log::channel('pos_info')->info($info_msg . ', 存在しない倉庫コード=' . $received_data['store_no']);

                return $this->is_skip_import = true;
            }

            // 入出庫データをグループ化
            $inventory_in_details = [];
            $inventory_out_details = [];

            foreach ($received_data['INVENTORY_DETAIL_DATA'] ?? [] as $detail) {
                $product = MasterProduct::query()
                    ->where('code', $detail->product_code)
                    ->first();

                if (empty($product)) {
                    $msg_not_exist_product = ', 存在しない商品コード=' . $detail->product_code
                        . ', 棚卸数量=' . $detail->inventory_stock
                        . ', 棚卸前=' . $detail->stock;
                    Log::channel('pos_info')->info($info_msg . $msg_not_exist_product);
                    $this->is_skip_import = true;

                    continue;
                }

                // POSからの差分を計算
                $pos_diff_quantity = $detail->inventory_stock - $detail->stock;

                // 差分が0の場合は処理をスキップ
                if ($pos_diff_quantity === 0) {
                    continue;
                }

                // 商品データと差分を配列に格納
                $detail_data = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => abs($pos_diff_quantity),
                    'pos_diff_quantity' => $pos_diff_quantity,
                ];

                // 入出庫タイプ(仕入)
                if ($pos_diff_quantity > 0) {
                    $inventory_in_details[] = $detail_data;
                }

                // 入出庫タイプ(納品)
                if ($pos_diff_quantity < 0) {
                    $inventory_out_details[] = $detail_data;
                }
            }

            // 仕入用
            if (count($inventory_in_details) > 0) {
                // 在庫データの登録
                $insert_data = [
                    'inout_date' => $inventory_date,
                    'inout_status' => 1,
                    'from_warehouse_id' => InventoryType::INVENTORY_IN,
                    'to_warehouse_id' => $warehouse->id,
                    'employee_id' => PosApiConst::POS_DATA_CREATER,
                    'updated_id' => PosApiConst::POS_DATA_CREATER,
                ];
                $inventory_data = InventoryData::query()->create($insert_data);

                $sort_index = 1;
                // 在庫詳細データの登録
                foreach ($inventory_in_details as $detail) {
                    InventoryDataDetail::query()->create([
                        'inventory_data_id' => $inventory_data->id,
                        'product_id' => $detail['product_id'],
                        'product_name' => $detail['product_name'],
                        'quantity' => $detail['quantity'],
                        'sort' => $sort_index++,
                        'note' => '在庫調整',
                    ]);

                    // 現在庫データの登録・更新
                    $inventory_stock_data = InventoryStockData::where('warehouse_id', $warehouse->id)
                        ->where('product_id', $detail['product_id'])
                        ->first();

                    if (empty($inventory_stock_data)) {
                        // 新規登録
                        InventoryStockData::query()->create([
                            'warehouse_id' => $warehouse->id,
                            'product_id' => $detail['product_id'],
                            'inventory_stocks' => $detail['pos_diff_quantity'],
                            'updated_id' => PosApiConst::POS_DATA_CREATER,
                        ]);
                    }

                    if (!empty($inventory_stock_data)) {
                        // 既存の在庫データがある場合は更新
                        $inventory_stock_data->update([
                            'inventory_stocks' => $inventory_stock_data->inventory_stocks + $detail['pos_diff_quantity'],
                        ]);
                    }
                }
            }

            // 納品用
            if (count($inventory_out_details) > 0) {
                // 在庫データの登録
                $insert_data = [
                    'inout_date' => $inventory_date,
                    'inout_status' => 1,
                    'from_warehouse_id' => $warehouse->id,
                    'to_warehouse_id' => InventoryType::INVENTORY_OUT,
                    'employee_id' => PosApiConst::POS_DATA_CREATER,
                    'updated_id' => PosApiConst::POS_DATA_CREATER,
                ];
                $inventory_data = InventoryData::query()->create($insert_data);

                $sort_index = 1;
                // 在庫詳細データの登録
                foreach ($inventory_out_details as $detail) {
                    InventoryDataDetail::query()->create([
                        'inventory_data_id' => $inventory_data->id,
                        'product_id' => $detail['product_id'],
                        'product_name' => $detail['product_name'],
                        'quantity' => $detail['quantity'],
                        'sort' => $sort_index++,
                        'note' => '在庫調整',
                    ]);

                    // 現在庫データの登録・更新
                    $inventory_stock_data = InventoryStockData::where('warehouse_id', $warehouse->id)
                        ->where('product_id', $detail['product_id'])
                        ->first();

                    if (empty($inventory_stock_data)) {
                        // 新規登録
                        InventoryStockData::query()->create([
                            'warehouse_id' => $warehouse->id,
                            'product_id' => $detail['product_id'],
                            'inventory_stocks' => $detail['pos_diff_quantity'],
                            'updated_id' => PosApiConst::POS_DATA_CREATER,
                        ]);
                    }

                    if (!empty($inventory_stock_data)) {
                        // 既存の在庫データがある場合は更新
                        $inventory_stock_data->update([
                            'inventory_stocks' => $inventory_stock_data->inventory_stocks + $detail['pos_diff_quantity'],
                        ]);
                    }
                }
            }

            return $this->is_skip_import;

        } catch (Exception $e) {
            Log::channel('pos_err')->error('【Error】ImportInventoryData | setInventoryReceipt :　' . $e->getMessage());
            throw $e;
        }
    }
}
