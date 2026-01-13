<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Logging;

use App\Helpers\DebugHelper;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

class CustomFormatter implements FormatterInterface
{
    /**
     * フォーマット変更
     *
     * @param array $record
     * @return string
     */
    public function format(LogRecord $record): string
    {
        $context = '';

        // 受注伝票
        if (isset($record['context']['created_orders_received'])) {
            // 受注伝票登録後のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['created_orders_received'],
                'debugReceivedOrder'
            );
        }
        if (isset($record['context']['updating_orders_received'])) {
            // 受注伝票更新前のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['updating_orders_received'],
                'debugReceivedOrder'
            );
        }
        if (isset($record['context']['updated_orders_received'])) {
            // 受注伝票更新後のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['updated_orders_received'],
                'debugReceivedOrder'
            );
        }

        // 在庫データ
        if (isset($record['context']['inventory_data'])) {
            // 在庫データ登録/更新時のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['inventory_data'],
                'debugInventoryData'
            );
        }

        // 現在庫数
        if (isset($record['context']['returned_inventory_stock_data'])) {
            // 返品処理時のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['returned_inventory_stock_data'],
                'debugInventoryStock'
            );
        }
        if (isset($record['context']['updating_inventory_stock_data'])) {
            // 現在庫数更新前のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['updating_inventory_stock_data'],
                'debugInventoryStock'
            );
        }
        if (isset($record['context']['updated_inventory_stock_data'])) {
            // 現在庫数更新後のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['updated_inventory_stock_data'],
                'debugInventoryStock'
            );
        }

        // 締在庫数
        if (isset($record['context']['inventory_data_closing'])) {
            // 締在庫数登録/更新時のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['inventory_data_closing'],
                'debugInventoryDataClosing'
            );
        }

        // 売上伝票
        if (isset($record['context']['sales_order'])) {
            // 売上伝票登録/更新時のデバッグ用フォーマット
            $context = DebugHelper::debugMultipleDataOutput(
                $record['context']['sales_order'],
                'debugSalesOrder'
            );
        }

        // ログのフォーマットをカスタマイズ
        return sprintf(
            '[%s] %s.%s: %s' . PHP_EOL . '%s',
            $record['datetime']->format('Y-m-d H:i:s'),
            $record['channel'],
            $record['level_name'],
            $record['message'],
            $context,
        );
    }

    /**
     * 多重配列用のフォーマット
     *
     * @param array $records
     * @return string
     */
    public function formatBatch(array $records): string
    {
        $formatted = '';
        foreach ($records as $record) {
            $formatted .= $this->format($record);
        }

        return $formatted;
    }
}
