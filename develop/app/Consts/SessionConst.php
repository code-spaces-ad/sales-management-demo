<?php

/**
 * セッション用定数 トレイト
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts;

/**
 * セッション用定数 トレイト
 */
trait SessionConst
{
    /** 参照URL
     *
     * @return string
     */
    public function refURL(): string
    {
        return 'reference_url.';
    }

    /** 在庫画面
     *
     * @return string
     */
    public function inventory(): string
    {
        return 'inventory.';
    }

    /** 共通使用キー(URL)
     *
     * @return string
     */
    public function refURLCommonKey(): string
    {
        return $this->refURL() . 'common_key';
    }

    /** 在庫処理用キー(URL)
     *
     * @return string
     */
    public function refURLInventoryKey(): string
    {
        return $this->refURL() . 'inventory_url';
    }

    /** マスター管理用キー(URL)
     *
     * @return string
     */
    public function refURLMasterKey(): string
    {
        return $this->refURL() . 'master_url';
    }

    /** システム設定用キー(URL)
     *
     * @return string
     */
    public function refURLSystemKey(): string
    {
        return $this->refURL() . 'system_url';
    }

    /** 在庫調整フラグ用キー
     *
     * @return string
     */
    public function refAdjustStocksKey(): string
    {
        return $this->inventory() . 'adjust_stocks';
    }
}
