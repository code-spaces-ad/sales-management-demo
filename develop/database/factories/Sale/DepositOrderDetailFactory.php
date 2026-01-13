<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Sale;

use App\Enums\DepositMethodType;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\DepositOrderDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepositOrderDetailFactory extends Factory
{
    protected $model = DepositOrderDetail::class;

    public function definition()
    {
        // 入金伝票取得（もしくは作成）
        $depositOrder = DepositOrder::inRandomOrder()->first() ?? DepositOrder::factory()->create();

        $amount_cash = 0;
        $amount_check = 0;
        $amount_transfer = 0;
        $amount_bill = 0;
        $amount_offset = 0;
        $amount_discount = 0;
        $amount_fee = 0;
        $amount_other = 0;

        $amountType = $this->faker->randomElement(array_keys(DepositMethodType::mappings()));
        if ($amountType == DepositMethodType::CASH) {
            $amount_cash = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == DepositMethodType::CHECK) {
            $amount_check = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == DepositMethodType::TRANSFER) {
            $amount_transfer = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == DepositMethodType::BILL) {
            $amount_bill = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == DepositMethodType::OFFSET) {
            $amount_offset = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == DepositMethodType::DISCOUNT) {
            $amount_discount = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == DepositMethodType::FEE) {
            $amount_fee = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == DepositMethodType::OTHER) {
            $amount_other = $this->faker->numberBetween(1000, 10000);
        }

        return [
            // 入金伝票ID
            'deposit_order_id' => $depositOrder->id,
            // 金額_現金
            'amount_cash' => $amount_cash,
            // 金額_小切手
            'amount_check' => $amount_check,
            // 金額_振込
            'amount_transfer' => $amount_transfer,
            // 金額_手形
            'amount_bill' => $amount_bill,
            // 金額_相殺
            'amount_offset' => $amount_offset,
            // 金額_値引
            'amount_discount' => $amount_discount,
            // 金額_手数料
            'amount_fee' => $amount_fee,
            // 金額_その他
            'amount_other' => $amount_other,
        ];
    }
}
