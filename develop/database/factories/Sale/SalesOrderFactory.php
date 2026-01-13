<?php

/**
 * @copyright © 2025 株式会社和香園
 */

namespace Database\Factories\Sale;

use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Enums\SalesClassification;
use App\Enums\TransactionType;
use App\Enums\TaxCalcType;
use App\Enums\TaxType;
use App\Enums\ReducedTaxFlagType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition()
    {
        // 伝票日付は過去分まで作成
        $startDate = Carbon::now()->subMonths(4)->startOfMonth();
        $endDate = Carbon::yesterday();
        $order_date = $this->faker->dateTimeBetween($startDate, $endDate);

        return [
            // 伝票日付
            'order_date' => $order_date->format('Y-m-d'),
            // 請求日
            'billing_date' => $order_date->format('Y-m-d'),
            // 部門ID
            'department_id' => MasterDepartment::inRandomOrder()->value('id'),
            // 事業所ID
            'office_facilities_id' => MasterOfficeFacility::inRandomOrder()->value('id'),
            //　得意先ID
            'customer_id' => MasterCustomer::inRandomOrder()->value('id'),
            // 税計算区分
            'tax_calc_type_id' => $this->faker->randomElement(array_keys(TaxCalcType::mappings())),
            // 取引種別ID
            'transaction_type_id' => $this->faker->randomElement(array_keys(TransactionType::mappings())),
            // 売上分類
            'sales_classification_id' => SalesClassification::CLASSIFICATION_SALE,
            // POS連携データ
            'link_pos' => $this->faker->boolean,
            // 登録者ID
            'creator_id' => $this->faker->numberBetween(1, 10),
            // 更新者ID
            'updated_id' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (SalesOrder $salesOrder) {
            $faker = $this->faker;
            $countDetail = $faker->numberBetween(1, 5);

            // 1件～5件の SalesOrderDetail を作成
            for ($count = 1; $count <= $countDetail; $count++) {
                SalesOrderDetail::factory()->create([
                    'sales_order_id' => $salesOrder->id,
                    'sort' => $count,
                ]);
            }

            $salesTotal = 0;
            $discount = 0;

            $salesTotalNormalOut = 0;
            $salesTotalReducedOut = 0;
            $salesTotalNormalIn = 0;
            $salesTotalReducedIn = 0;
            $salesTotalFree = 0;

            $salesTaxNormalOut = 0;
            $salesTaxReducedOut = 0;
            $salesTaxNormalIn = 0;
            $salesTaxReducedIn = 0;

            // 作成した SalesOrderDetail のデータを取得
            $sales_order_details = SalesOrderDetail::where('sales_order_id', $salesOrder->id)->get();

            // SalesOrderDetail の金額から SalesOrder の必要な金額の集計
            foreach ($sales_order_details ?? [] as $detail) {
                $salesTotal += $detail->sub_total;
                $discount += $detail->discount;

                if ($detail->tax_type_id == TaxType::OUT_TAX) {
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::NOT_REDUCED_TAX) {
                        $salesTotalNormalOut += $detail->sub_total;
                        $salesTaxNormalOut += $detail->sub_total_tax;
                    }
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::REDUCED_TAX) {
                        $salesTotalReducedOut += $detail->sub_total;
                        $salesTaxReducedOut += $detail->sub_total_tax;
                    }
                }

                if ($detail->tax_type_id == TaxType::IN_TAX) {
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::NOT_REDUCED_TAX) {
                        $salesTotalNormalIn += $detail->sub_total;
                        $salesTaxNormalIn += $detail->sub_total_tax;
                    }
                    if ($detail->reduced_tax_flag === ReducedTaxFlagType::REDUCED_TAX) {
                        $salesTotalReducedIn += $detail->sub_total;
                        $salesTaxReducedIn += $detail->sub_total_tax;
                    }
                }

                if ($detail->tax_type_id == TaxType::TAX_EXEMPT) {
                    $salesTotalFree += $detail->sub_total;
                }
            }

            // SalesOrder 更新
            $salesOrder->update([
                'order_number' => $salesOrder->id,
                'sales_total' => $salesTotal,
                'discount' => $discount,
                'sales_total_normal_out' => $salesTotalNormalOut,
                'sales_total_reduced_out' => $salesTotalReducedOut,
                'sales_total_normal_in' => $salesTotalNormalIn,
                'sales_total_reduced_in' => $salesTotalReducedIn,
                'sales_total_free' => $salesTotalFree,
                'sales_tax_normal_out' => $salesTaxNormalOut,
                'sales_tax_reduced_out' => $salesTaxReducedOut,
                'sales_tax_normal_in' => $salesTaxNormalIn,
                'sales_tax_reduced_in' => $salesTaxReducedIn,
            ]);
        });
    }
}
