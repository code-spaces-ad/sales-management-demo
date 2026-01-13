<?php

/**
 * 請求締処理用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Ajax;

use App\Events\CompletedChargeClosingEvent;
use App\Jobs\ChargeClosingJob;
use App\Services\ChargeClosingService;
use Illuminate\Http\JsonResponse;

/**
 * 請求締処理用サービス
 */
class AjaxChargeClosingServices
{
    protected ChargeClosingService $service;

    /**
     * serviceをインスタンス
     *
     * @param ChargeClosingService $service
     */
    public function __construct(ChargeClosingService $service)
    {
        $this->service = $service;
    }

    /**
     * 締処理
     *
     * @param array $customer_ids
     * @param array $conditions
     * @return JsonResponse
     */
    public function closing(array $customer_ids, array $conditions): JsonResponse
    {
        // 締処理
        foreach ($customer_ids as $customer_id) {
            ChargeClosingJob::dispatch(
                $customer_id,
                $conditions,
                $this->service
            );
        }

        event(new CompletedChargeClosingEvent('completed'));

        return response()->json('success');
    }
}
