<?php

/**
 * POS受信（POS→販売管理）用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\PosApiServices;
use Illuminate\Http\Request;

class PosReceiveController extends Controller
{
    protected PosApiServices $services;

    public function __construct(PosApiServices $services)
    {
        parent::__construct();
        $this->services = $services;
    }

    /**
     * 販売データ受信
     *
     * @param Request $request
     * @return mixed
     */
    public function receiveSales(Request $request): mixed
    {
        return $this->services->receiveSales($request);
    }

    /**
     * 棚卸データ受信
     *
     * @param Request $request
     * @return mixed
     */
    public function receiveInventory(Request $request): mixed
    {
        return $this->services->receiveInventory($request);
    }

    /**
     * 工場出庫データ受信
     *
     * @param Request $request
     * @return mixed
     */
    public function receiveFactoryShipping(Request $request): mixed
    {
        return $this->services->receiveFactoryShipping($request);
    }

    /**
     * 仕入データ受信
     *
     * @param Request $request
     * @return mixed
     */
    public function receivePurchase(Request $request): mixed
    {
        return $this->services->receivePurchase($request);
    }
}
