<?php

/**
 * POS送信（販売管理→POS）用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Api;

use App\Services\Api\PosApiServices;
use Illuminate\Http\Request;

class PosSendController
{
    protected PosApiServices $services;

    public function __construct(PosApiServices $services)
    {
        $this->services = $services;
    }

    /**
     * 商品マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendProductMaster(Request $request)
    {
        return $this->services->sendProductMaster($request);
    }

    /**
     * 得意先マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendCustomerMaster(Request $request)
    {
        return $this->services->sendCustomerMaster($request);
    }

    /**
     * 得意先別単価マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendUnitPriceByCustomerMaster(Request $request)
    {
        return $this->services->sendUnitPriceByCustomerMaster($request);
    }

    /**
     * 担当者マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendEmployeesMaster(Request $request)
    {
        return $this->services->sendEmployeesMaster($request);
    }
}
