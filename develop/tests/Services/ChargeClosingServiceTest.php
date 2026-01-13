<?php

namespace Tests\Services;

use App\Services\ChargeClosingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChargeClosingServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** @var ChargeClosingService */
    var $target = null;

    public function setUp(): void
    {
//        parent::setUp();
//
//        $this->target = $this->app->make(ChargeClosingService::class);
    }

    /**
     * 締処理のテスト.
     */
    public function testChargeClosingServiceTest()
    {
        $customer_id = 1;
        $charge_year_month = "2022-01";
        $closing_date = 25;

        $chargeClosingService = new ChargeClosingService();
        $chargeClosingService->setCustomerId($customer_id)
            ->setClosingDate($charge_year_month, $closing_date)
            ->process();

        $this->assertTrue(true);
    }
}
