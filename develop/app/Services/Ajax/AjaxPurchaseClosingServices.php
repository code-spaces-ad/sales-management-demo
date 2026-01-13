<?php

namespace App\Services\Ajax;

use App\Events\CompletedPurchaseClosingEvent;
use App\Jobs\PurchaseClosingJob;
use App\Services\PurchaseClosingService;
use Illuminate\Http\JsonResponse;

class AjaxPurchaseClosingServices
{
    protected PurchaseClosingService $service;

    /**
     * serviceをインスタンス
     *
     * @param PurchaseClosingService $service
     */
    public function __construct(PurchaseClosingService $service)
    {
        $this->service = $service;
    }

    /**
     * 締処理
     *
     * @param array $supplier_ids
     * @param array $conditions
     * @return JsonResponse
     */
    public function closing(array $supplier_ids, array $conditions): JsonResponse
    {
        // 締処理
        foreach ($supplier_ids as $supplier_id) {
            PurchaseClosingJob::dispatch(
                $supplier_id,
                $conditions,
                $this->service
            );
        }

        event(new CompletedPurchaseClosingEvent('completed'));

        return response()->json('success');
    }
}
