<?php

/**
 * 請求締処理解除ジョブ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Jobs;

use App\Services\ChargeClosingService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ChargeClosingCancelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $charge_data_ids;

    /**
     * Create a new job instance.
     *
     * @param array $charge_data_ids
     */
    public function __construct(array $charge_data_ids)
    {
        $this->charge_data_ids = $charge_data_ids;
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
        foreach ($this->charge_data_ids as $charge_data_id) {
            $closingService = new ChargeClosingService();
            try {
                $result = $closingService->setChargeDataId($charge_data_id)->cancel();

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
