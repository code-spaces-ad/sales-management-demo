<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Services\Ajax\AjaxChargeClosingServices;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AjaxChargeClosingController extends Controller
{
    protected AjaxChargeClosingServices $service;

    /**
     * AjaxChargeClosingController constructor.
     */
    public function __construct(AjaxChargeClosingServices $service)
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
                $request->input('customer_ids'),
                $request->input('searchForm')
            ),
        ]);
    }
}
