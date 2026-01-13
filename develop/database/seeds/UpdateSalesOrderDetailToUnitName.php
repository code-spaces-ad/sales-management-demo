<?php

use App\Models\Sale\SalesOrderDetail;
use Illuminate\Database\Seeder;

class UpdateSalesOrderDetailToUnitName extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $details = SalesOrderDetail::where('unit_name', '')->get();
        foreach ($details as $detail) {
            $detail->unit_name = $detail->mProduct->mProductUnit->mUnit->name ?? '';
            // 更新日時は更新しない
            $detail->timestamps = false;
            $detail->save();
        }
    }
}
