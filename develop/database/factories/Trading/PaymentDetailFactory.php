<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Trading;

use App\Enums\PaymentMethodType;
use App\Models\Trading\Payment;
use App\Models\Trading\PaymentDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentDetailFactory extends Factory
{
    protected $model = PaymentDetail::class;

    public function definition()
    {
        // 仕入伝票取得（もしくは作成）
        $payment = Payment::inRandomOrder()->first() ?? Payment::factory()->create();

        $amount_cash = 0;
        $amount_check = 0;
        $amount_transfer = 0;
        $amount_bill = 0;
        $amount_offset = 0;
        $amount_discount = 0;
        $amount_fee = 0;
        $amount_other = 0;

        $amountType = $this->faker->randomElement(array_keys(PaymentMethodType::mappings()));
        if ($amountType == PaymentMethodType::CASH) {
            $amount_cash = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == PaymentMethodType::CHECK) {
            $amount_check = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == PaymentMethodType::TRANSFER) {
            $amount_transfer = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == PaymentMethodType::BILL) {
            $amount_bill = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == PaymentMethodType::OFFSET) {
            $amount_offset = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == PaymentMethodType::DISCOUNT) {
            $amount_discount = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == PaymentMethodType::FEE) {
            $amount_fee = $this->faker->numberBetween(1000, 10000);
        }
        if ($amountType == PaymentMethodType::OTHER) {
            $amount_other = $this->faker->numberBetween(1000, 10000);
        }

        return [
            // 支払伝票ID
            'payment_id' => $payment->id,
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
