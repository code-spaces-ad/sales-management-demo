<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Trading;

use App\Enums\OrderStatus;
use App\Enums\TaxCalcType;
use App\Models\Master\MasterSupplier;
use App\Models\Trading\PurchaseOrder;
use App\Models\Trading\PurchaseOrderDetail;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Enums\SalesClassification;
use App\Enums\TransactionType;
use App\Enums\TaxType;
use App\Enums\ReducedTaxFlagType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition()
    {
        // 発注日付は過去分まで作成
        $startDate = Carbon::now()->subMonths(4)->startOfMonth();
        $endDate = Carbon::yesterday();
        $order_date = $this->faker->dateTimeBetween($startDate, $endDate);

        // 仕入分類は完全ランダムではなく、「0：販売」を多めに
        $keys = array_keys(SalesClassification::mappings());
        $weighted = array_merge(
            array_fill(0, 7, $keys[0]),
            array_fill(0, 1, $keys[1]),
            array_fill(0, 1, $keys[2]),
            array_fill(0, 1, $keys[3]),
        );

        return [
            // 発注日付
            'order_date' => $order_date->format('Y-m-d'),
            // 状態
            'order_status' => $this->faker->randomElement(array_keys(OrderStatus::mappings())),
            // 仕入先ID
            'supplier_id' => MasterSupplier::inRandomOrder()->value('id'),
            // 部門ID
            'department_id' => MasterDepartment::inRandomOrder()->value('id'),
            // 事業所ID
            'office_facilities_id' => MasterOfficeFacility::inRandomOrder()->value('id'),
            // 取引種別ID
            'transaction_type_id' => $this->faker->randomElement(array_keys(TransactionType::mappings())),
            // 税計算区分
            'tax_calc_type_id' => $this->faker->randomElement(array_keys(TaxCalcType::mappings())),
            // 仕入分類
            'purchase_classification_id' => $this->faker->randomElement($weighted),
            // POS連携データ
            'link_pos' => $this->faker->boolean,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (PurchaseOrder $purchaseOrder) {
            $faker = $this->faker;
            $countDetail = $faker->numberBetween(1, 5);

            // 1件～5件の PurchaseOrderDetail を作成
            for ($count = 1; $count <= $countDetail; $count++) {
                PurchaseOrderDetail::factory()->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'sort' => $count,
                ]);
            }

            $purchaseTotal = 0;
            $discount = 0;

            $purchaseTotalNormalOut = 0;
            $purchaseTotalReducedOut = 0;
            $purchaseTotalNormalIn = 0;
            $purchaseTotalReducedIn = 0;
            $purchaseTotalFree = 0;

            $purchaseTaxNormalOut = 0;
            $purchaseTaxReducedOut = 0;
            $purchaseTaxNormalIn = 0;
            $purchaseTaxReducedIn = 0;

            // 作成した PurchaseOrderDetail のデータを取得
            $purchase_order_details = PurchaseOrderDetail::where('purchase_order_id', $purchaseOrder->id)->get();

            // PurchaseOrderDetail の金額から PurchaseOrder の必要な金額の集計
            foreach ($purchase_order_details ?? [] as $detail) {
                $purchaseTotal += $detail->sub_total;
                $discount += $detail->discount;

                if ($detail->tax_type_id == TaxType::OUT_TAX) {
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::NOT_REDUCED_TAX) {
                        $purchaseTotalNormalOut += $detail->sub_total;
                        $purchaseTaxNormalOut += $detail->sub_total_tax;
                    }
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::REDUCED_TAX) {
                        $purchaseTotalReducedOut += $detail->sub_total;
                        $purchaseTaxReducedOut += $detail->sub_total_tax;
                    }
                }

                if ($detail->tax_type_id == TaxType::IN_TAX) {
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::NOT_REDUCED_TAX) {
                        $purchaseTotalNormalIn += $detail->sub_total;
                        $purchaseTaxNormalIn += $detail->sub_total_tax;
                    }
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::REDUCED_TAX) {
                        $purchaseTotalReducedIn += $detail->sub_total;
                        $purchaseTaxReducedIn += $detail->sub_total_tax;
                    }
                }

                if ($detail->tax_type_id == TaxType::TAX_EXEMPT) {
                    $purchaseTotalFree += $detail->sub_total;
                }
            }

            // PurchaseOrder 更新
            $purchaseOrder->update([
                'order_number' => $purchaseOrder->id,
                'purchase_total' => $purchaseTotal,
                'discount' => $discount,
                'purchase_total_normal_out' => $purchaseTotalNormalOut,
                'purchase_total_reduced_out' => $purchaseTotalReducedOut,
                'purchase_total_normal_in' => $purchaseTotalNormalIn,
                'purchase_total_reduced_in' => $purchaseTotalReducedIn,
                'purchase_total_free' => $purchaseTotalFree,
                'purchase_tax_normal_out' => $purchaseTaxNormalOut,
                'purchase_tax_reduced_out' => $purchaseTaxReducedOut,
                'purchase_tax_normal_in' => $purchaseTaxNormalIn,
                'purchase_tax_reduced_in' => $purchaseTaxReducedIn,
            ]);
        });
    }
}
