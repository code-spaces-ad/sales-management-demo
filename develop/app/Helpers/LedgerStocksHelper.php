<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Sale\Ledger\LedgerStocks;
use App\Models\Sale\SalesOrder;
use App\Models\Trading\PurchaseOrder;
use Illuminate\Support\Facades\DB;

/**
 * 帳簿在庫数用ヘルパークラス
 */
class LedgerStocksHelper
{
    /**
     * 帳簿在庫数を登録
     *
     * @param string $order_date
     * @return void
     */
    public static function registLedgerStock(string $order_date): void
    {
        $closing_ym = DateHelper::changeDateFormat($order_date, 'Ym');

        $purchase_order = PurchaseOrder::query()
            ->selectRaw('SUM(purchase_order_details.quantity) AS ledger_stocks')
            ->addSelect('purchase_order_details.product_id AS product_id')
            ->join('purchase_order_details', function ($join) {
                $join->on('purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
                    ->whereNull('purchase_order_details.deleted_at');
            })
            ->where('purchase_orders.order_date', 'LIKE', $order_date . '%')
            ->groupBy('purchase_order_details.product_id');

        $sales_order = SalesOrder::query()
            ->selectRaw('-SUM(sales_order_details.quantity) AS ledger_stocks')
            ->addSelect('sales_order_details.product_id AS product_id')
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->where('sales_orders.order_date', 'LIKE', $order_date . '%')
            ->groupBy('sales_order_details.product_id');

        $ledger_stocks = DB::table($sales_order)
            ->unionAll($purchase_order);

        $ledger_stocks = DB::table($ledger_stocks)
            ->groupBy('product_id')
            ->get(
                [
                    DB::Raw('SUM(ledger_stocks) AS ledger_stocks'),
                    DB::Raw('product_id'),
                ]);

        foreach ($ledger_stocks ?? [] as $detail) {
            $ledgerStocks = new LedgerStocks();
            $ledger_stock = $ledgerStocks->getLastLedgerStocks($detail->product_id, $closing_ym)->ledger_stocks ?? 0;

            $ledgerStocks->query()
                ->updateOrInsert(
                    [
                        'product_id' => $detail->product_id,
                        'closing_ym' => $closing_ym,
                    ],
                    [
                        'ledger_stocks' => $ledger_stock + $detail->ledger_stocks ?? 0,
                        'deleted_at' => null,
                    ]);
        }
    }
}
