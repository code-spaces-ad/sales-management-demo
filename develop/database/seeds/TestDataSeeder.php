<?php

/**
 * @copyright © 2025 CodeSpaces
 */
namespace Database\Seeders;

use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataClosing;
use App\Models\Inventory\InventoryDataDetail;
use App\Models\Inventory\InventoryDataStatusHistory;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\DepositOrderDetail;
use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use App\Models\Trading\PaymentDetail;
use App\Models\Trading\PurchaseOrder;
use App\Models\Trading\Payment;
use App\Models\Trading\PurchaseOrderDetail;
use App\Models\Trading\PurchaseOrderStatusHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Test Data Seeder Class
 */
class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // production時は実行しない
        if (app()->isProduction()) {
            return;
        }

        DB::statement('SET foreign_key_checks = 0');

        // 在庫情報
        InventoryDataClosing::query()->truncate();
        InventoryStockData::query()->truncate();
        InventoryDataStatusHistory::query()->truncate();
        InventoryDataDetail::query()->truncate();
        InventoryData::query()->truncate();

        $products = MasterProduct::all();
        $offices = MasterOfficeFacility::all();
        $closingYm = Carbon::today()->startOfMonth()->subMonths(4)->format('Ym');

        // 締在庫情報
        foreach ($offices as $office) {
            foreach ($products as $product) {
                InventoryDataClosing::factory()->create([
                    // 倉庫ID
                    'warehouse_id' => $office->id,
                    // 商品ID
                    'product_id' => $product->id,
                    // 締年月
                    'closing_ym' => $closingYm,
                    // 締在庫数
                    'closing_stocks' => '50.0000',
                ]);
            }
        }

        // 3か月前データを作成
        $startDate = Carbon::today()->startOfMonth()->subMonths(3);
        $endDate = $startDate->copy()->endOfMonth();
        $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
        $randomDate = Carbon::createFromTimestamp($randomTimestamp);
        InventoryData::factory()->count(10)->create([
            // 入出庫日付
            'inout_date' => $randomDate->format('Y/m/d'),
        ]);

        // 2か月前データを作成
        $startDate = Carbon::today()->startOfMonth()->subMonths(2);
        $endDate = $startDate->copy()->endOfMonth();
        $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
        $randomDate = Carbon::createFromTimestamp($randomTimestamp);
        InventoryData::factory()->count(8)->create([
            // 入出庫日付
            'inout_date' => $randomDate->format('Y/m/d'),
        ]);

        // 1か月前データを作成
        $startDate = Carbon::today()->startOfMonth()->subMonths(1);
        $endDate = $startDate->copy()->endOfMonth();
        $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
        $randomDate = Carbon::createFromTimestamp($randomTimestamp);
        InventoryData::factory()->count(8)->create([
            // 入出庫日付
            'inout_date' => $randomDate->format('Y/m/d'),
        ]);

        // 当月データを作成
        $startDate = Carbon::today()->startOfMonth();
        $endDate = Carbon::today();
        $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
        $randomDate = Carbon::createFromTimestamp($randomTimestamp);
        InventoryData::factory()->count(8)->create([
            // 入出庫日付
            'inout_date' => $randomDate->format('Y/m/d'),
        ]);

        // 販売データ
        SalesOrderDetail::query()->truncate();
        SalesOrder::query()->truncate();

        SalesOrder::factory()->count(100)->create();

        // 仕入データ
        PurchaseOrderStatusHistory::query()->truncate();
        PurchaseOrderDetail::query()->truncate();
        PurchaseOrder::query()->truncate();

        PurchaseOrder::factory()->count(100)->create();

        // 入金データ
        DepositOrderDetail::query()->truncate();
        DepositOrder::query()->truncate();

        DepositOrder::factory()->count(100)->create();

        // 支払データ
        PaymentDetail::query()->truncate();
        Payment::query()->truncate();

        Payment::factory()->count(100)->create();

        DB::statement('SET foreign_key_checks = 1');
    }
}
