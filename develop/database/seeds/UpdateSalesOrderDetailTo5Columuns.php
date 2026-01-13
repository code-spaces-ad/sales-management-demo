<?php

use App\Helpers\MathHelper;
use App\Models\Sale\SalesOrderDetail;
use Illuminate\Database\Seeder;

class UpdateSalesOrderDetailTo5Columuns extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $details = SalesOrderDetail::where('unit_price_decimal_digit', 0)
            ->where('quantity_decimal_digit', 0)
            ->where('quantity_rounding_method_id', 0)
            ->where('amount_rounding_method_id', 0)
            ->where('sub_total', 0)
            ->get();
        foreach ($details as $detail) {

            // 単価小数桁数
            $detail->unit_price_decimal_digit = $detail->mProduct->unit_price_decimal_digit;
            // 金額小数桁数
            $detail->quantity_decimal_digit = $detail->mProduct->quantity_decimal_digit;

            // 数量端数処理
            $detail->quantity_rounding_method_id = $detail->mProduct->quantity_rounding_method_id;
            // 金額端数処理
            $detail->amount_rounding_method_id = $detail->mProduct->amount_rounding_method_id;

            // 端数処理
            $roundinged_price = MathHelper::getRoundingValue($detail->unit_price, $detail->unit_price_decimal_digit, $detail->amount_rounding_method_id);
            $roundinged_quantity = MathHelper::getRoundingValue($detail->quantity, $detail->quantity_decimal_digit, $detail->quantity_rounding_method_id);
            // 小計金額
            $detail->sub_total = $roundinged_price * $roundinged_quantity;

            // 更新日時は更新しない
            $detail->timestamps = false;
            $detail->save();
        }
        \Log::info('UpdateSalesOrderDetailTo5Columuns:'.count($details).'件処理しました。');
    }
}
