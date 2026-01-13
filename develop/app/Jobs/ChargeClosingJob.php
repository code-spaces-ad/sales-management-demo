<?php

namespace App\Jobs;

use App\Events\ChargeClosingEvent;
use App\Helpers\RedrawHelper;
use App\Services\ChargeClosingService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChargeClosingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $success_count = 0;

    private int $skip_count = 0;

    private int $failed_count = 0;

    private int $user_id;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $customer_id, private array $conditions, private ChargeClosingService $service
    ) {
        $this->user_id = auth()->id();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $result = $this->service->setCustomerId($this->customer_id)
                ->setClosingDate($this->conditions['charge_date'], $this->conditions['closing_date'])
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

        ChargeClosingEvent::dispatch(
            $this->customer_id,
            (new RedrawHelper())
                ->redrawBillingCustomerTag(
                    $this->customer_id,
                    $this->conditions
                )
        );
    }
}
