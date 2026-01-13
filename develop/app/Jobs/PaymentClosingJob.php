<?php

/**
 * 請求締処理ジョブ
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

class PaymentClosingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $supplier_ids;

    protected $charge_year_month;

    protected $closing_date;

    /**
     * Create a new job instance.
     *
     * @param array $supplier_ids
     * @param string $charge_year_month
     * @param int $closing_date
     */
    public function __construct(array $supplier_ids, string $charge_year_month, int $closing_date)
    {
        $this->supplier_ids = $supplier_ids;
        $this->charge_year_month = $charge_year_month;
        $this->closing_date = $closing_date;
    }

    /**
     * ジョブの実行
     *
     * @return JsonResponse
     */
    public function handle(): JsonResponse
    {
        $success_count = 0;
        $skip_count = 0;
        $failed_count = 0;

        // 締処理
        foreach ($this->supplier_ids as $supplier_id) {
            $closingService = new PaymentClosingService();
            try {
                $result = $closingService->setSupplierId($supplier_id)
                    ->setClosingDate($this->charge_year_month, $this->closing_date)
                    ->closing();

                $content = json_decode($result->content(), true);
                if ($content['message'] === 'success') {
                    ++$success_count;
                }
                if ($content['message'] === 'skip') {
                    ++$skip_count;
                }

            } catch (Exception $e) {
                Log::error($e->getMessage());
                ++$failed_count;
            }
        }

        $json = [
            'success' => $success_count,
            'skip' => $skip_count,
            'failed' => $failed_count,
        ];

        return response()->json($json);
    }
}
