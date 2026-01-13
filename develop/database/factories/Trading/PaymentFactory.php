<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Trading;

use App\Models\Master\MasterSupplier;
use App\Models\Trading\Payment;
use App\Models\Trading\PaymentDetail;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Enums\TransactionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        // 伝票日付は過去分まで作成
        $startDate = Carbon::now()->subMonths(4)->startOfMonth();
        $endDate = Carbon::yesterday();
        $order_date = $this->faker->dateTimeBetween($startDate, $endDate);

        return [
            // 伝票日付
            'order_date' => $order_date->format('Y-m-d'),
            // 仕入先ID
            'supplier_id' => MasterSupplier::inRandomOrder()->value('id'),
            // 部門ID
            'department_id' => MasterDepartment::inRandomOrder()->value('id'),
            // 事業所ID
            'office_facilities_id' => MasterOfficeFacility::inRandomOrder()->value('id'),
            // 取引種別ID
            'transaction_type_id' => $this->faker->randomElement(array_keys(TransactionType::mappings())),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Payment $payment) {
            $faker = $this->faker;
            $countDetail = $faker->numberBetween(1, 5);

            // 1件～5件の PaymentDetail を作成
            for ($count = 1; $count <= $countDetail; $count++) {
                PaymentDetail::factory()->create([
                    'payment_id' => $payment->id,
                ]);
            }

            // 作成した PaymentDetail のデータを取得
            $payment_details = PaymentDetail::where('payment_id', $payment->id)->get();

            // PaymentDetail の金額から Payment の必要な金額の集計
            $payment_total = 0;
            foreach ($payment_details ?? [] as $detail) {
                $payment_total += $detail->amount_cash +
                    $detail->amount_check +
                    $detail->amount_transfer +
                    $detail->amount_bill +
                    $detail->amount_offset +
                    $detail->amount_discount +
                    $detail->amount_fee +
                    $detail->amount_other;
            }

            // Payment 更新
            $payment->update([
                'order_number' => $payment->id,
                'payment' => $payment_total,
            ]);
        });
    }
}
