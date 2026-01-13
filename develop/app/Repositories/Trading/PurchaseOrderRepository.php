<?php

/**
 * 仕入伝票用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Trading;

use App\Base\BaseRepository;
use App\Helpers\ProductHelper;
use App\Models\Trading\PurchaseOrder;
use App\Models\Trading\PurchaseOrderDetail;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 仕入伝票用リポジトリ
 */
class PurchaseOrderRepository extends BaseRepository
{
    protected Model $model;

    /**
     * 仕入伝票モデルをインスタンス
     *
     * @param PurchaseOrder $model
     */
    public function __construct(PurchaseOrder $model)
    {
        parent::__construct($model);

        $this->model = $model;
    }

    /**
     * 検索結果を取得(ページネーション)
     *
     * @param array $input_data
     * @return LengthAwarePaginator
     */
    public function getSearchResult(array $input_data): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['mSupplier', 'mUpdated'])
            ->orderByDesc('order_number')   // 仕入番号の降順
            ->searchCondition($input_data)
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 合計値を取得
     *
     * @param array $input_data
     * @return Collection
     */
    public function getSearchResultTotal(array $input_data): Collection
    {
        return $this->model->query()
            ->searchCondition($input_data)
            ->get([
                DB::raw('SUM( purchase_total ) AS purchase_total'),
                DB::raw('SUM( discount ) AS discount'),
                DB::raw('SUM( purchase_tax_normal_out + purchase_tax_reduced_out ) AS purchase_tax_total'),
            ]);
    }

    /**
     * 仕入伝票新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createPurchaseOrder(array $array): Model
    {
        return $this->model->query()->create($array);
    }

    /**
     * 仕入伝票詳細新規登録
     *
     * @param Model $model
     * @param array $details
     * @return PurchaseOrder
     */
    public function createPurchaseOrderDetails(Model $model, array $details): PurchaseOrder
    {
        return tap($model, function ($model) use ($details) {
            $model->purchaseOrderDetail()->createMany($details);
        });
    }

    /**
     * 仕入伝票更新
     *
     * @param Model $model
     * @param array $array
     * @return Model
     */
    public function updatePurchaseOrder(Model $model, array $array): Model
    {
        return tap($model)
            ->update($array);
    }

    /**
     * 仕入伝票詳細更新
     *
     * @param Model $model
     * @param array $detail
     * @return PurchaseOrder
     */
    public function updatePurchaseOrderDetails(Model $model, array $detail): PurchaseOrder
    {
        // 仕入伝票詳細を更新
        return tap($model, function ($model) use ($detail) {
            $model->purchaseOrderDetail()
                ->updateOrInsert(
                    [
                        'purchase_order_id' => $model->id,
                        'sort' => $detail['sort'],
                    ],
                    $detail
                );
        });
    }

    /**
     * 更新に伴う仕入伝票詳細削除
     *
     * @param Model $model
     * @param array $sorts
     * @return PurchaseOrder
     */
    public function deleteOrderDetailsForUpdate(Model $model, array $sorts): PurchaseOrder
    {
        return tap($model, function ($model) use ($sorts) {
            $model->purchaseOrderDetail()
                ->whereNotIn('sort', $sorts)
                ->delete();
        });
    }

    /**
     * 仕入伝票削除
     *
     * @param Model $model
     * @return bool|null
     */
    public function deleteOrdersReceived(Model $model): ?bool
    {
        $model->purchaseOrderDetail()
            ->delete();

        return $model
            ->delete();
    }

    /**
     * 商品単価登録更新
     *
     * @param int $supplier_id
     * @param PurchaseOrderDetail $order_detail
     * @return void
     */
    public function upsertUnitPrice(int $supplier_id, PurchaseOrderDetail $order_detail): void
    {
        $product_id = $order_detail->product_id;
        $unit_price = $order_detail->unit_price;
        $unit_name = $order_detail->unit_name;

        // 商品マスタと同じ価格かどうか
        if (ProductHelper::existMasterProductPurchaseUnitPrice($product_id, $unit_price)) {
            // マスタと一致する場合は削除
            ProductHelper::deleteSupplierUnitPrice($supplier_id, $product_id, $unit_name);
        } else {
            // 単価マスタに登録済みかどうか
            if (!ProductHelper::existSupplierUnitPriceSameAmount($supplier_id, $product_id, $unit_name, $unit_price)) {
                // 得意先単価を登録/更新する
                ProductHelper::upsertSupplierUnitPrice($supplier_id, $product_id, $unit_name, $unit_price);
            }
        }
    }
}
