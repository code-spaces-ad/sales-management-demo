<?php

namespace App\Helpers;

use App\Models\Sale\SalesOrder;
use App\Models\Trading\PurchaseOrder;
use Illuminate\Support\Collection;

class RedrawHelper
{
    /**
     * DOM書き換え用のViewを取得(請求締処理)
     *
     * @param string $customer_id
     * @param array $conditions
     * @return string
     */
    public function redrawBillingCustomerTag(string $customer_id, array $conditions): string
    {
        return view('components.invoice.billing_customer_list', [
            'search_condition_input_data' => [
                'charge_date' => $conditions['charge_date'],
                'closing_date' => $conditions['closing_date'],
            ],
            /** 検索結果 */
            'search_result' => [
                'charge_data' => $this->getClosingCustomer($customer_id, $conditions),
            ],
        ])->render();
    }

    /**
     * 締処理
     *
     * @param string $customer_id
     * @param array $conditions
     * @return Collection
     */
    public function getClosingCustomer(string $customer_id, array $conditions): Collection
    {
        // 締年月日の範囲年月取得
        [$charge_date_start, $charge_date_end] = ClosingDateHelper::getChargeCloseTermDate($conditions['charge_date'], $conditions['closing_date']);

        return SalesOrder::getTargetClosingCustomerData(
            array_merge($conditions, [
                'customer_id' => $customer_id,
            ]),
            $charge_date_start, $charge_date_end
        );
    }

    /**
     * DOM書き換え用のViewを取得(仕入締処理)
     *
     * @param string $supplier_id
     * @param array $conditions
     * @return string
     */
    public function redrawSupplierTag(string $supplier_id, array $conditions): string
    {
        return view('components.purchase_invoice.billing_supplier_list', [
            'search_condition_input_data' => [
                'charge_date' => $conditions['purchase_date'],
                'closing_date' => $conditions['closing_date'],
            ],
            /** 検索結果 */
            'search_result' => [
                'purchase_data' => $this->getClosingSupplier($supplier_id, $conditions),
            ],
        ])->render();
    }

    /**
     * 締処理
     *
     * @param string $supplier_id
     * @param array $conditions
     * @return Collection
     */
    public function getClosingSupplier(string $supplier_id, array $conditions): Collection
    {
        // 締年月日の範囲年月取得
        [$charge_date_start, $charge_date_end] = ClosingDateHelper::getChargeCloseTermDate($conditions['purchase_date'], $conditions['closing_date']);

        return PurchaseOrder::getTargetClosingSupplierData(
            array_merge($conditions, [
                'supplier_id' => $supplier_id,
            ]),
            $charge_date_start, $charge_date_end
        );
    }
}
