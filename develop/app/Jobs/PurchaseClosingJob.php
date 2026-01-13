<?php

namespace App\Jobs;

use App\Events\PurchaseClosingEvent;
use App\Helpers\RedrawHelper;
use App\Services\PurchaseClosingService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PurchaseClosingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $success_count = 0;

    private int $skip_count = 0;

    private int $failed_count = 0;

    private int $user_id;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $supplier_id, private array $conditions, private PurchaseClosingService $service
    ) {
        $this->user_id = auth()->id();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $result = $this->service->setSupplierId($this->supplier_id)
                ->setClosingDate($this->conditions['purchase_date'], $this->conditions['closing_date'])
                ->setDepartmentAndOfficeFacilitiesId($this->conditions['department_id'], $this->conditions['office_facility_id'])
                ->setUserId($this->user_id)
                ->closing();

            $content = json_decode($result->content(), true);
            if ($content['message'] === 'success') {
                ++$this->success_count;
            }
            if ($content['message'] === 'skip') {
                ++$this->skip_count;
            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
            ++$this->failed_count;
        }

        PurchaseClosingEvent::dispatch(
            $this->supplier_id,
            (new RedrawHelper())
                ->redrawSupplierTag(
                    $this->supplier_id,
                    $this->conditions
                )
        );
    }
}
