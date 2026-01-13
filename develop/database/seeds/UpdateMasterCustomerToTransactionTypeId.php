<?php

use App\Models\Master\MasterCustomer;
use Illuminate\Database\Seeder;

class UpdateMasterCustomerToTransactionTypeId extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = MasterCustomer::where('transaction_type_id', 0)->get();
        foreach ($customers as $customer) {
            // 「売掛」セット
            $customer->transaction_type_id = TransactionType::ON_ACCOUNT;
            // 更新日時は更新しない
            $customer->timestamps = false;
            $customer->save();
        }
    }
}
