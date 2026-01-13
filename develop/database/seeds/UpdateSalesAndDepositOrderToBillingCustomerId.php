<?php

use App\Models\Sale\DepositOrder;
use App\Models\Sale\SalesOrder;
use Illuminate\Database\Seeder;

class UpdateSalesAndDepositOrderToBillingCustomerId extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 売上伝票データの請求先セット
        $salesOrders = SalesOrder::whereNull('billing_customer_id')->withTrashed()->get();
        foreach ($salesOrders as $order) {
            $order->billing_customer_id = $order->mCustomer->billing_customer_id;
            // 更新日時は更新しない
            $order->timestamps = false;
            $order->save();
        }

        // 入金伝票データの請求先セット
        $depositOrders = DepositOrder::whereNull('billing_customer_id')->withTrashed()->get();
        foreach ($depositOrders as $order) {
            $order->billing_customer_id = $order->mCustomer->billing_customer_id;
            // 更新日時は更新しない
            $order->timestamps = false;
            $order->save();
        }
    }
}
