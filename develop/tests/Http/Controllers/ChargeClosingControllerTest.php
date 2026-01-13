<?php

namespace Tests\Http\Controllers;

use App\Models\Master\MasterUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class ControllerTestBase
 * @package Tests\Http\Controllers\Auth
 */
class ChargeClosingControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        // 認証
        $this->actingAs(MasterUser::find(1));
    }

    /**
     * 締処理のテスト.
     */
    public function testGetChargeClosing()
    {
        $params = [
            'customer_id' => 1,
            'charge_date' => '2022-02',
        ];

        $response = $this->call('GET', route('api.charge_closing.is_closing'), $params);
        $this->assertTrue(json_decode($response->getContent(), true)[0]);
    }

}
