<?php

/**
 * 売上伝票用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Sale;

use App\Enums\TaxCalcType;
use App\Helpers\ProductHelper;
use App\Models\Master\MasterCustomerPrice;
use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use DB;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 売上伝票用リポジトリ
 */
class OrderRepository
{
    protected Model $model;

    /**
     * 売上伝票モデルをインスタンス
     *
     * @param SalesOrder $model
     */
    public function __construct(SalesOrder $model)
    {
        $this->model = $model;
    }

    /**
     * 検索結果を取得
     *
     * @param array $conditions
     * @return LengthAwarePaginator
     */
    public function getSearchResult(array $conditions): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['mBranch', 'salesOrderDetail'])
            ->searchCondition($conditions)
            ->latest('order_number')
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 検索結果を取得（合計値）
     *
     * @param array $conditions
     * @return int
     */
    public function getSearchResultTotal(array $conditions): int
    {
        return $this->model->query()
            ->searchCondition($conditions)
            ->sum('sales_total');
    }

    /**
     * 売上伝票新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createSalesOrder(array $array): Model
    {
        // 詳細登録の為、現在のモデルをインスタンス
        return $this->model = $this->model->query()->create($array);
    }

    /**
     * 売上伝票詳細新規登録
     *
     * @param Model $order
     * @param array $details
     * @return Model
     */
    public function createSalesOrderDetails(Model $order, array $details): Model
    {
        return tap($order, function ($model) use ($details) {
            $model->salesOrderDetail()->createMany($details);
        });
    }

    /**
     * 売上伝票更新
     *
     * @param Model $order
     * @param array $array
     * @return Model
     */
    public function updateSalesOrder(Model $order, array $array): Model
    {
        return tap($order)->update($array);
    }

    /**
     * 売上伝票詳細更新
     *
     * @param Model $order
     * @param array $detail
     * @return Model
     */
    public function updateSalesOrderDetails(Model $order, array $detail): Model
    {
        // 売上伝票詳細を更新
        return tap($order, function ($model) use ($detail) {
            $model->salesOrderDetail()
                ->updateOrInsert(
                    [
                        'sales_order_id' => $model->id,
                        'sort' => $detail['sort'],
                    ],
                    $detail
                );
        });
    }

    /**
     * 更新に伴う売上伝票詳細削除
     *
     * @param Model $order
     * @param array $sorts
     * @return Model
     */
    public function deleteOrderDetailsForUpdate(Model $order, array $sorts): Model
    {
        return tap($order, function ($model) use ($sorts) {
            $model->salesOrderDetail()
                ->whereNotIn('sort', $sorts)
                ->delete();
        });
    }

    /**
     * 売上伝票削除
     *
     * @param Model $order
     * @return bool|null
     */
    public function deleteSalesOrder(Model $order): ?bool
    {
        $order->salesOrderDetail()->delete();

        return $order->delete();
    }

    /**
     * 締対象となる得意先毎の売上(売掛)伝票情報を取得
     *
     * @param array $sales_order_ids
     * @param string $start_date
     * @param string $end_date
     * @return Collection
     */
    public function getTargetClosingBillingData(array $sales_order_ids, string $start_date, string $end_date): Collection
    {
        return $this->model->query()
            ->TargetClosingData($sales_order_ids, TaxCalcType::BILLING, $start_date, $end_date)
            ->groupBy(
                [
                    'sales_order_details.tax_type_id',
                    'sales_order_details.consumption_tax_rate',
                    'sales_order_details.reduced_tax_flag',
                    'sales_order_details.rounding_method_id',
                ]
            )
            ->get([
                DB::raw('SUM( sales_order_details.sub_total ) AS sales_total'),
                DB::raw('sales_order_details.tax_type_id'),
                DB::raw('sales_order_details.consumption_tax_rate'),
                DB::raw('sales_order_details.reduced_tax_flag'),
                DB::raw('sales_order_details.rounding_method_id'),
            ]);
    }

    /**
     * 締対象となる得意先毎の売上(売掛)伝票情報を取得
     *
     * @param array $sales_order_ids
     * @param string $start_date
     * @param string $end_date
     * @return Collection
     */
    public function getTargetClosingOrderData(array $sales_order_ids, string $start_date, string $end_date): Collection
    {
        return $this->model->query()
            ->TargetClosingData($sales_order_ids, TaxCalcType::ORDER, $start_date, $end_date)
            ->groupBy(
                [
                    'sales_orders.id',
                    'sales_order_details.tax_type_id',
                    'sales_order_details.consumption_tax_rate',
                    'sales_order_details.reduced_tax_flag',
                    'sales_order_details.rounding_method_id',
                ]
            )
            ->get(
                [
                    DB::raw('sales_orders.id'),
                    DB::raw('SUM( sales_order_details.sub_total ) AS sales_total'),
                    DB::raw('sales_order_details.tax_type_id'),
                    DB::raw('sales_order_details.consumption_tax_rate'),
                    DB::raw('sales_order_details.reduced_tax_flag'),
                    DB::raw('sales_order_details.rounding_method_id'),
                ]
            );
    }

    /**
     * 締対象となる得意先毎の売上(売掛)明細情報を取得
     *
     * @param array $sales_order_ids
     * @param string $start_date
     * @param string $end_date
     * @return Collection
     */
    public function getTargetClosingDetailData(array $sales_order_ids, string $start_date, string $end_date): Collection
    {
        return $this->model->query()
            ->TargetClosingData($sales_order_ids, TaxCalcType::DETAIL, $start_date, $end_date)
            ->get(
                [
                    DB::raw('sales_orders.id'),
                    DB::raw('sales_order_details.sub_total AS sales_total'),
                    DB::raw('sales_order_details.tax_type_id'),
                    DB::raw('sales_order_details.consumption_tax_rate'),
                    DB::raw('sales_order_details.reduced_tax_flag'),
                    DB::raw('sales_order_details.rounding_method_id'),
                ]
            );
    }

    /**
     * 商品単価登録更新
     *
     * @param int $customer_id
     * @param string $order_date
     * @param SalesOrderDetail $detail
     * @return void
     */
    public function upsertCustomerPrice(int $customer_id, string $order_date, SalesOrderDetail $detail): void
    {
        $product_id = $detail->product_id;
        $unit_price = $detail->unit_price;

        // 商品マスタと同じ価格かどうか
        if (ProductHelper::existMasterProductUnitPrice($product_id, $unit_price)) {
            // マスタと一致する場合は削除
            ProductHelper::deleteCustomerPrice($customer_id, $product_id);
        } else {
            // 単価マスタに登録済みかどうか
            if (!ProductHelper::existCustomerPriceSameAmount($customer_id, $product_id, $unit_price)) {
                // 得意先単価を登録/更新する
                ProductHelper::upsertCustomerPrice($customer_id, $order_date, $detail);
            }
        }
    }

    /**
     * 商品単価登録更新
     * ※CodeSpacesでは使用しない
     *
     * @param int $customer_id
     * @param SalesOrderDetail $detail
     * @return void
     */
    public function upsertUnitPrice(int $customer_id, SalesOrderDetail $detail): void
    {
        $product_id = $detail['product_id'];
        $unit_price = $detail['unit_price'];
        $unit_name = $detail['unit_name'];

        // 商品マスタと同じ価格かどうか
        if (ProductHelper::existMasterProductUnitPrice($product_id, $unit_price)) {
            // マスタと一致する場合は削除
            ProductHelper::deleteCustomerUnitPrice($customer_id, $product_id, $unit_name);
        } else {
            // 単価マスタに登録済みかどうか
            if (!ProductHelper::existCustomerUnitPriceSameAmount($customer_id, $product_id, $unit_name, $unit_price)) {
                // 得意先単価を登録/更新する
                ProductHelper::upsertCustomerUnitPrice($customer_id, $product_id, $unit_name, $unit_price);
            }
        }
    }

    //    /**
    //     * 最終単価更新（得意先別単価マスタ）
    //     *
    //     * @param int $customer_id
    //     * @param SalesOrderDetail $detail
    //     * @return void
    //     * @throws GuzzleException
    //     */
    //    public function upsertCustomerPrice(int $customer_id, SalesOrderDetail $detail): void
    //    {
    //        $product_id = $detail['product_id'];
    //        $unit_name = $detail['unit_name'];
    //        $unit_price = $detail['unit_price'];
    //        $updated_at = $detail['updated_at'];
    //
    //        // 商品マスタと同じ価格かどうか
    //        if (ProductHelper::existMasterProductUnitPrice($product_id, $unit_price)) {
    //            // マスタと一致する場合は得意先別単価マスタのレコードを削除
    //            ProductHelper::deleteCustomerPrice($customer_id, $product_id, $unit_name);
    //            return;
    //        }
    //
    //        $exist_data = MasterCustomerPrice::query()
    //            ->where('customer_id', $customer_id)
    //            ->where('product_id', $product_id)
    //            ->where('unit_name', $unit_name)
    //            ->first();
    //
    //        $update_flg = true;
    //        if (!isset($exist_data)) {
    //            $update_flg = false;
    //        }
    //
    //        if ($update_flg && $updated_at < $exist_data->sales_date) {
    //            // 得意先別単価マスタの方が新しい場合は更新なし
    //            return;
    //        }
    //
    //        // 得意先別単価マスタに同額のデータが登録済みかどうか
    //        if (!ProductHelper::existCustomerPriceSameAmount($customer_id, $product_id, $unit_name, $updated_at, $unit_price)) {
    //            // 得意先単価を登録/更新する
    //            ProductHelper::upsertCustomerPrice($customer_id, $product_id, $unit_name, $updated_at, $unit_price, $update_flg);
    //        }
    //
    //    }
}
