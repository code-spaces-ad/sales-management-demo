<?php

/**
 * 在庫処理用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Ajax;

use App\Consts\SessionConst;
use App\Enums\InventoryType;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterProduct;
use App\Repositories\Master\MasterProductRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 在庫処理用サービス
 */
class AjaxInventoryStocksDataServices
{
    use SessionConst;

    protected MasterProductRepository $repository;

    /**
     * Repositoryをインスタンス
     *
     * @param MasterProductRepository $repository
     */
    public function __construct(MasterProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 現在庫更新(Ajax使用) + /Middleware/RegenerateToken.php でリフレッシュされたcsrfトークンをreturn
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function setInventory(Request $request): JsonResponse
    {
        DB::beginTransaction();
        $purchase_unit_price = $this->repository->getPurchaseUnitPrice($request->product_id);
        $purchase_total_price = $request->inventory_value * $purchase_unit_price;
        try {
            // 在庫データ作成
            self::setInventoryDatas($request);

            (new InventoryStockData())->query()
                ->updateOrInsert(
                    [
                        'warehouse_id' => $request->warehouse_id,
                        'product_id' => $request->product_id,
                    ],
                    [
                        'inventory_stocks' => $request->inventory_value,
                        'purchase_total_price' => $purchase_total_price,
                        /** 更新者ID */
                        'updated_id' => Auth::user()->id,
                    ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        return response()->json([
            'token' => csrf_token(),
            'purchase_total_price' => $purchase_total_price,
        ]);
    }

    /**
     * 在庫データ作成(Ajax使用)
     *
     * @param Request $request
     * @return void
     *
     * @throws Exception
     */
    public function setInventoryDatas(Request $request): void
    {
        $inventory_stocks = (new InventoryStockData())->query()
            ->where('warehouse_id', $request->warehouse_id)
            ->where('product_id', $request->product_id)
            ->value('inventory_stocks');

        $inventory_data = InventoryData::query()->create([
            'inout_date' => now()->format('Y-m-d'),
            'inout_status' => 1,
            'from_warehouse_id' => InventoryType::INVENTORY_ADJUST,
            'to_warehouse_id' => $request->warehouse_id ?? null,
            'employee_id' => Auth::user()->employee_id,
            'note' => '在庫調整',
            'updated_id' => Auth::user()->id,
        ]);
        $inventory_data->inventoryDataDetail()->create([
            'product_id' => $request->product_id,
            'product_name' => MasterProduct::query()->find($request->product_id)->name,
            'quantity' => $inventory_stocks - $request->inventory_value,
            'sort' => 0,
            'note' => '在庫調整',
        ]);
    }
}
