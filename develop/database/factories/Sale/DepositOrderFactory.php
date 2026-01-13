<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Sale;

use App\Models\Sale\DepositOrder;
use App\Models\Sale\DepositOrderDetail;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Enums\TransactionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepositOrderFactory extends Factory
{
    protected $model = DepositOrder::class;

    public function definition()
    {
        // 伝票日付は過去分まで作成
        $startDate = Carbon::now()->subMonths(4)->startOfMonth();
        $endDate = Carbon::yesterday();
        $order_date = $this->faker->dateTimeBetween($startDate, $endDate);

        return [
            // 伝票日付
            'order_date' => $order_date->format('Y-m-d'),
            // 取引種別ID
            'transaction_type_id' => $this->faker->randomElement(array_keys(TransactionType::mappings())),
            //　得意先ID
            'customer_id' => MasterCustomer::inRandomOrder()->value('id'),
            //　請求先ID
            'billing_customer_id' => MasterCustomer::inRandomOrder()->value('id'),
            // 部門ID
            'department_id' => MasterDepartment::inRandomOrder()->value('id'),
            // 事業所ID
            'office_facilities_id' => MasterOfficeFacility::inRandomOrder()->value('id'),
            // 登録者ID
            'creator_id' => $this->faker->numberBetween(1, 10),
            // 更新者ID
            'updated_id' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (DepositOrder $depositOrder) {
            $faker = $this->faker;
            $countDetail = $faker->numberBetween(1, 5);

            // 1件～5件の DepositOrderDetail を作成
            for ($count = 1; $count <= $countDetail; $count++) {
                DepositOrderDetail::factory()->create([
                    'deposit_order_id' => $depositOrder->id,
                ]);
            }

            // 作成した DepositOrderDetail のデータを取得
            $deposit_order_details = DepositOrderDetail::where('deposit_order_id', $depositOrder->id)->get();

            // DepositOrderDetail の金額から DepositOrder の必要な金額の集計
            $deposit = 0;
            foreach ($deposit_order_details ?? [] as $detail) {
                $deposit += $detail->amount_cash +
                    $detail->amount_check +
                    $detail->amount_transfer +
                    $detail->amount_bill +
                    $detail->amount_offset +
                    $detail->amount_discount +
                    $detail->amount_fee +
                    $detail->amount_other;
            }

            // DepositOrder 更新
            $depositOrder->update([
                'order_number' => $depositOrder->id,
                'deposit' => $deposit,
            ]);
        });
    }
}
