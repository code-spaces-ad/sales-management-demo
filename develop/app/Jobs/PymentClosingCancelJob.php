<?php

/**
 * 請求締処理解除ジョブ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Jobs;

use App\Services\PaymentClosingService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class PaymentClosingCancelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $purchase_order_ids;

    /**
     * Create a new job instance.
     *
     * @param array $purchase_order_ids
     */
    public function __construct(array $purchase_order_ids)
    {
        $this->purchase_order_ids = $purchase_order_ids;
    }

    /**
     * ジョブの実行
     *
     * @return JsonResponse
     */
    public function handle(): JsonResponse
    {
        $success_count = 0;
        $failed_count = 0;

        // 締処理
        foreach ($this->purchase_order_ids as $purchase_order_id) {
            $chargeClosingService = new PaymentClosingService();
            try {
                $result = $chargeClosingService->setChargeDataId($purchase_order_id)->cancel();

                $content = json_decode($result->content(), true);
                if ($content['message'] === 'success') {
                    ++$success_count;
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
                ++$failed_count;
            }
        }

        $json = [
            'success' => $success_count,
            'failed' => $failed_count,
        ];

        return response()->json($json);
    }
}
