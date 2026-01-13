<?php

use App\Models\Master\MasterCustomer;
use App\Models\Invoice\ChargeData;
use Illuminate\Database\Seeder;

class UpdateMasterCustomerToBillingBalance extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = MasterCustomer::all();
        foreach ($customers as $customer) {
            if (!ChargeData::where('customer_id', $customer->id)
                ->OrderByDesc('created_at')->exists()) {
                continue;
            }

            // 請求データがある場合のみセットする
            $customer->billing_balance = ChargeData::where('customer_id', $customer->id)
                ->OrderByDesc('created_at')
                ->value('charge_total');
            // 更新日時は更新しない
            $customer->timestamps = false;
            $customer->save();
        }
    }
}
