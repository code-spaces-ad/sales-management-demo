<?php

/**
 * デバッグヘルパークラス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * デバッグヘルパークラス
 */
class DebugHelper
{
    /**
     * 受注伝票登録/更新時のデバッグ用フォーマット
     *
     * @param array $detail
     * @return string
     */
    public static function debugReceivedOrder(array $detail): string
    {
        $delivery_date = isset($detail['delivery_date'])
            ? Carbon::parse($detail['delivery_date'])->format('Y/m/d')
            : null;

        $message = '倉庫ID: ' . $detail['warehouse_id'] . PHP_EOL;
        $message .= '商品ID: ' . $detail['product_id'] . PHP_EOL;
        $message .= '数量: ' . $detail['quantity'] . PHP_EOL;
        $message .= '納品日: ' . $delivery_date . PHP_EOL;
        $message .= '売上確定: ' . $detail['sales_confirm'] . PHP_EOL;
        $message .= '作成日時: ' . $detail['created_at'] . PHP_EOL;
        $message .= '更新日時: ' . $detail['updated_at'] . PHP_EOL;
        $message .= PHP_EOL;

        return $message;
    }

    /**
     * 在庫データ登録/更新時のデバッグ用フォーマット
     *
     * @param Model $detail
     * @return string
     */
    public static function debugInventoryData(Model $detail): string
    {
        $inout_date = isset($detail->inout_date)
            ? Carbon::parse($detail->inout_date)->format('Y/m/d')
            : null;

        $message = 'ID: ' . $detail->id . PHP_EOL;
        $message .= '入出庫日: ' . $inout_date . PHP_EOL;
        $message .= '移動元倉庫ID: ' . $detail->from_warehouse_id . PHP_EOL;
        $message .= '移動先倉庫ID: ' . $detail->to_warehouse_id . PHP_EOL;
        $message .= '担当者ID: ' . $detail->employee_id . PHP_EOL;
        foreach ($detail->inventoryDataDetail()->get() as $value) {
            $message .= '　商品ID: ' . $value->product_id . PHP_EOL;
            $message .= '　数量: ' . $value->quantity . PHP_EOL;
        }
        $message .= '更新者ID: ' . $detail->updated_id . PHP_EOL;
        $message .= '作成日時: ' . $detail->created_at . PHP_EOL;
        $message .= '更新日時: ' . $detail->updated_at . PHP_EOL;
        $message .= PHP_EOL;

        return $message;
    }

    /**
     * 現在庫データ登録/更新時のデバッグ用フォーマット
     *
     * @param Model $detail
     * @return string
     */
    public static function debugInventoryStock(Model $detail): string
    {
        $message = 'ID: ' . $detail->id . PHP_EOL;
        $message .= '倉庫ID: ' . $detail->warehouse_id . PHP_EOL;
        $message .= '商品ID: ' . $detail->product_id . PHP_EOL;
        $message .= '数量: ' . $detail->inventory_stocks . PHP_EOL;
        $message .= '更新者ID: ' . $detail->updated_id . PHP_EOL;
        $message .= '作成日時: ' . $detail->created_at . PHP_EOL;
        $message .= '更新日時: ' . $detail->updated_at . PHP_EOL;
        $message .= PHP_EOL;

        return $message;
    }

    /**
     * 締在庫数登録/更新時のデバッグ用フォーマット
     *
     * @param Model $detail
     * @return string
     */
    public static function debugInventoryDataClosing(Model $detail): string
    {
        $closing_ym = isset($detail->closing_ym)
            ? Carbon::parse($detail->closing_ym . '01')->format('Y年m月')
            : null;

        $message = 'ID: ' . $detail->id . PHP_EOL;
        $message .= '倉庫ID: ' . $detail->warehouse_id . PHP_EOL;
        $message .= '商品ID: ' . $detail->product_id . PHP_EOL;
        $message .= '数量: ' . $detail->closing_stocks . PHP_EOL;
        $message .= '締年月: ' . $closing_ym . PHP_EOL;
        $message .= '作成日時: ' . $detail->created_at . PHP_EOL;
        $message .= '更新日時: ' . $detail->updated_at . PHP_EOL;
        $message .= PHP_EOL;

        return $message;
    }

    /**
     * 売上伝票登録/更新時のデバッグ用フォーマット
     *
     * @param Model $detail
     * @return string
     */
    public static function debugSalesOrder(Model $detail): string
    {
        $message = 'ID: ' . $detail->id . PHP_EOL;
        foreach ($detail->salesOrderDetail()->get() as $value) {
            $message .= '　商品ID: ' . $value->product_id . PHP_EOL;
            $message .= '　数量: ' . $value->quantity . PHP_EOL;
        }
        $message .= '作成日時: ' . $detail->created_at . PHP_EOL;
        $message .= '更新日時: ' . $detail->updated_at . PHP_EOL;
        $message .= PHP_EOL;

        return $message;
    }

    /**
     * 多重配列用のフォーマット変更
     *
     * @param array|null $debug_array ログ出力対象のデータ(配列)
     * @param string $method 本クラス内のいずれかのメソッド名
     * @return void
     */
    public static function debugMultipleDataOutput(?array $debug_array, string $method): string
    {
        $output_format = '';
        foreach ($debug_array as $data) {
            if (is_null($data)) {
                continue;
            }
            $output_format .= self::$method($data);
        }

        return $output_format;
    }
}
