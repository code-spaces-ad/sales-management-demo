<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

/**
 * メッセージ用ヘルパークラス
 */
class MessageHelper
{
    /**
     * 登録時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @param string $category
     * @return string
     */
    public static function getStoreMessage(bool $error_flag, string $code, string $category = 'common'): string
    {
        $message = config('consts.message.' . $category . '.store_success');
        if ($error_flag) {
            $message = config('consts.message.' . $category . '.store_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 更新時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @param string $category
     * @return string
     */
    public static function getUpdateMessage(bool $error_flag, string $code, string $category = 'common'): string
    {
        $message = config('consts.message.' . $category . '.update_success');
        if ($error_flag) {
            $message = config('consts.message.' . $category . '.update_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 削除時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @param string $category
     * @return string
     */
    public static function getDestroyMessage(bool $error_flag, string $code, string $category = 'common'): string
    {
        $message = config('consts.message.' . $category . '.destroy_success');
        if ($error_flag) {
            $message = config('consts.message.' . $category . '.destroy_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 売上伝票用登録時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getOrderStoreMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.order.store_success');
        if ($error_flag) {
            $message = config('consts.message.order.store_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 売上伝票用更新時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getOrderUpdateMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.order.update_success');
        if ($error_flag) {
            $message = config('consts.message.order.update_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 売上伝票用削除時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getOrderDestroyMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.order.destroy_success');
        if ($error_flag) {
            $message = config('consts.message.order.destroy_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 入金伝票用登録時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getDepositStoreMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.deposit.store_success');
        if ($error_flag) {
            $message = config('consts.message.deposit.store_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 入金伝票用更新時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getDepositUpdateMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.deposit.update_success');
        if ($error_flag) {
            $message = config('consts.message.deposit.update_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 入金伝票用削除時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getDepositDestoryMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.deposit.destroy_success');
        if ($error_flag) {
            $message = config('consts.message.deposit.destroy_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 支払伝票用登録時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getPaymentStoreMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.payment.store_success');
        if ($error_flag) {
            $message = config('consts.message.payment.store_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 支払伝票用更新時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getPaymentUpdateMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.payment.update_success');
        if ($error_flag) {
            $message = config('consts.message.payment.update_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 支払伝票用削除時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @return string
     */
    public static function getPaymentDestoryMessage(bool $error_flag, string $code): string
    {
        $message = config('consts.message.payment.destroy_success');
        if ($error_flag) {
            $message = config('consts.message.payment.destroy_failed');
        }

        return str_replace('**order_number', $code, $message);
    }

    /**
     * 締処理実施時メッセージ
     *
     * @param array $result
     * @return array
     */
    public static function getChargeClosingStoreMessage(array $result): array
    {
        $error_flg = false;
        $message = config('consts.message.charge_closing.store_success');
        $message_detail = "\r\n・正常処理件数：" . $result['success'] . '件';
        if ($result['skip'] > 0) {
            $message_detail .= "\r\n・未処理件数：" . $result['skip'] . '件';
        }
        if ($result['failed'] > 0) {
            $message_detail .= "\r\n・エラー件数：" . $result['failed'] . '件';
            $message = config('consts.message.charge_closing.store_failed');
            $error_flg = true;
        }

        return [($message . $message_detail), $error_flg];
    }

    /**
     * 締解除時メッセージ
     *
     * @param array $result
     * @return array
     */
    public static function getChargeClosingCancelMessage(array $result): array
    {
        $error_flg = false;
        $message = config('consts.message.charge_closing.cancel_success');
        $message_detail = "\r\n・解除件数：" . $result['success'] . '件';
        if ($result['failed'] > 0) {
            $message_detail .= "\r\n・エラー件数：" . $result['failed'] . '件';
            $message = config('consts.message.charge_closing.cancel_failed');
            $error_flg = true;
        }

        return [($message . $message_detail), $error_flg];
    }

    /**
     * 仕入締処理実施時メッセージ
     *
     * @param array $result
     * @return array
     */
    public static function getPurchaseOrderClosingStoreMessage(array $result): array
    {
        $error_flg = false;
        $message = config('consts.message.purchase_closing.store_success');
        $message_detail = "\r\n・正常処理件数：" . $result['success'] . '件';
        if ($result['skip'] > 0) {
            $message_detail .= "\r\n・未処理件数：" . $result['skip'] . '件';
        }
        if ($result['failed'] > 0) {
            $message_detail .= "\r\n・エラー件数：" . $result['failed'] . '件';
            $message = config('consts.message.purchase_closing.store_failed');
            $error_flg = true;
        }

        return [($message . $message_detail), $error_flg];
    }

    /**
     * 仕入締解除時メッセージ
     *
     * @param array $result
     * @return array
     */
    public static function getPurchaseOrderClosingCancelMessage(array $result): array
    {
        $error_flg = false;
        $message = config('consts.message.purchase_closing.cancel_success');
        $message_detail = "\r\n・解除件数：" . $result['success'] . '件';
        if ($result['failed'] > 0) {
            $message_detail .= "\r\n・エラー件数：" . $result['failed'] . '件';
            $message = config('consts.message.purchase_closing.cancel_failed');
            $error_flg = true;
        }

        return [($message . $message_detail), $error_flg];
    }

    /**
     * マスター用登録時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @param string $name
     * @return string
     */
    public static function getMasterStoreMessage(bool $error_flag, string $code, string $name): string
    {
        $message = config('consts.message.master.store_success');
        if ($error_flag) {
            $message = config('consts.message.master.store_failed');
        }
        $message = str_replace('**code', $code, $message);

        return str_replace('**name', $name, $message);
    }

    /**
     * マスター用更新時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @param string $name
     * @return string
     */
    public static function getMasterUpdateMessage(bool $error_flag, string $code, string $name): string
    {
        $message = config('consts.message.master.update_success');
        if ($error_flag) {
            $message = config('consts.message.master.update_failed');
        }
        $message = str_replace('**code', $code, $message);

        return str_replace('**name', $name, $message);
    }

    /**
     * マスター用削除時メッセージ
     *
     * @param bool $error_flag
     * @param string $code
     * @param string $name
     * @return string
     */
    public static function getMasterDestroyMessage(bool $error_flag, string $code, string $name): string
    {
        $message = config('consts.message.master.destroy_success');
        if ($error_flag) {
            $message = config('consts.message.master.destroy_failed');
        }
        $message = str_replace('**code', $code, $message);

        return str_replace('**name', $name, $message);
    }
}
