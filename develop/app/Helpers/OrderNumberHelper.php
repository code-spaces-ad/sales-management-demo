<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\OrderType;
use App\Models\Sale\SalesOrder;
use App\Models\Trading\PurchaseOrder;

/**
 * 伝票番号ヘルパークラス
 */
class OrderNumberHelper
{
    /**
     * 空番検索(伝票番号)
     *
     * @param int $available_number
     * @param int $order_type
     * @return int
     */
    public static function getOrderNumber(int $available_number, int $order_type): int
    {
        // 売上伝票の場合
        if ($order_type === OrderType::SALES) {
            $data = SalesOrder::query()
                ->withTrashed()
                ->orderBy('order_number', 'asc')
                ->get('order_number');
        }

        // 仕入伝票の場合
        if ($order_type === OrderType::PURCHASE) {
            $data = PurchaseOrder::query()
                ->withTrashed()
                ->orderBy('order_number', 'asc')
                ->get('order_number');
        }

        $order_number_list = count($data->pluck('order_number')->toArray()) !== 0 ? $data->pluck('order_number')->toArray() : [0 => '0'];

        // $available_number 未満の order_number を削除
        $order_number_list = collect($order_number_list)->filter(function ($value, $available_number) {
            return $value >= $available_number;
        });

        foreach ($order_number_list as $order_number) {
            if (intval($order_number) === $available_number) {
                ++$available_number;

                continue;
            }
            break;
        }

        return $available_number;
    }
}
