<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Helpers;

use App\Helpers\ClosingDateHelper;
use Tests\TestCase;

/**
 * Class ClosingDateHelperTest
 * @package Tests\Helpers
 */
class ClosingDateHelperTest extends TestCase
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 末日締め
     * @test
     */
    public function testGetChargeCloseTermDate_CloseDayMonthEnd()
    {
        // 末日
        $closing_date = 0;

        // １月(31日までの月)
        $charge_year_month = '2022-01';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2022-01-01', $start_date->format('Y-m-d'));
        $this->assertEquals('2022-01-31', $end_date->format('Y-m-d'));

        // 2月（通常:28日までの月）
        $charge_year_month = '2022-02';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2022-02-01', $start_date->format('Y-m-d'));
        $this->assertEquals('2022-02-28', $end_date->format('Y-m-d'));

        // 2月（うるう年：29日までの月）
        $charge_year_month = '2020-02';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2020-02-01', $start_date->format('Y-m-d'));
        $this->assertEquals('2020-02-29', $end_date->format('Y-m-d'));

        // 4月(30日までの月)
        $charge_year_month = '2022-04';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2022-04-01', $start_date->format('Y-m-d'));
        $this->assertEquals('2022-04-30', $end_date->format('Y-m-d'));
    }

    /**
     * 25日締め
     * @test
     */
    public function testGetChargeCloseTermDate_CloseDay25()
    {
        // 末日
        $closing_date = 25;

        // １月(31日までの月)
        $charge_year_month = '2022-01';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2021-12-26', $start_date->format('Y-m-d'));
        $this->assertEquals('2022-01-25', $end_date->format('Y-m-d'));

        // 2月（通常:28日までの月）
        $charge_year_month = '2022-02';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2022-01-26', $start_date->format('Y-m-d'));
        $this->assertEquals('2022-02-25', $end_date->format('Y-m-d'));

        // 2月（うるう年：29日までの月）
        $charge_year_month = '2020-02';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2020-01-26', $start_date->format('Y-m-d'));
        $this->assertEquals('2020-02-25', $end_date->format('Y-m-d'));

        // 4月(30日までの月)
        $charge_year_month = '2022-04';
        [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($charge_year_month, $closing_date);
        $this->assertEquals('2022-03-26', $start_date->format('Y-m-d'));
        $this->assertEquals('2022-04-25', $end_date->format('Y-m-d'));
    }
}
