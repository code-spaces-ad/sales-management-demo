<?php

namespace App\Http\Controllers\Ajax;

use App\Consts\SessionConst;
use App\Http\Controllers\Controller;
use App\Services\Ajax\AjaxInventoryStocksDataServices;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AjaxInventoryStocksDataController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    protected AjaxInventoryStocksDataServices $service;

    /**
     * AjaxInventoryStocksDataController constructor.
     */
    public function __construct(AjaxInventoryStocksDataServices $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * セッション保存(Ajax使用)
     *
     * @param Request $request
     * @return void
     */
    public function setSession(Request $request): void
    {
        Session::put($this->refAdjustStocksKey(), $request->adjust_stocks);
    }

    /**
     * 現在庫更新(Ajax使用) + /Middleware/RegenerateToken.php でリフレッシュされたcsrfトークンをreturn
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function setInventory(Request $request): JsonResponse
    {
        return $this->service->setInventory($request);
    }
}
