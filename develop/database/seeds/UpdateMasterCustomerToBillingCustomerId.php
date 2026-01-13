<?php

use App\Models\Master\MasterCustomer;
use Illuminate\Database\Seeder;

class UpdateMasterCustomerToBillingCustomerId extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = MasterCustomer::whereNull('billing_customer_id')->withTrashed()->get();
        foreach ($customers as $customer) {
            $customer->billing_customer_id = $customer->id;
            // 更新日時は更新しない
            $customer->timestamps = false;
            $customer->save();
        }
    }
}
