<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Services\Ajax\AjaxPurchaseClosingServices;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AjaxPurchaseClosingController extends Controller
{
    protected AjaxPurchaseClosingServices $service;

    /**
     * AjaxPurchaseClosingServices constructor.
     */
    public function __construct(AjaxPurchaseClosingServices $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * 締処理
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function closingJob(Request $request): JsonResponse
    {
        // 一覧画面へリダイレクト
        return response()->json([
            'token' => csrf_token(),
            'result' => $this->service->closing(
                $request->input('supplier_ids'),
                $request->input('searchForm')
            ),
        ]);
    }
}
