<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Sale;

use App\Models\Master\MasterCustomer;
use App\Models\Invoice\ChargeDataDepositOrder;
use App\Models\Sale\DepositOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class ChargeDataDepositOrderTest
 * @package Tests\Models\Sale
 */
class ChargeDataDepositOrderTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var ChargeDataDepositOrder
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see ChargeDataDepositOrder
     */
    protected $default_data = [];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // DatabaseSeeder 実行
        $this->seed();

        // デフォルトデータセット
        $model = factory(DepositOrder::class, 10)->create();  // ※売上伝票で作成
        // デフォルトデータ保持
        $default_data = DB::table('charge_data_deposit_order')
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new ChargeDataDepositOrder();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testAll_データがあること()
    {
        $expected = $this->default_data;
        $actual = $this->target
            ->get()
            ->toArray();
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testAll_必要なフィールドがあること()
    {
        $data = $this->target->all()->toArray();

        $expected = [
            /** 請求データID */
            'charge_data_id',
            /** 入金伝票ID */
            'deposit_order_id',
            /** 作成日時 */
            'created_at',
            /** 更新日時 */
            'updated_at',
            /** 削除日時 */
            'deleted_at'
        ];
        $actual = array_keys($data[0]);     // ※一つ目のデータでチェック
        $this->assertSame($expected, $actual);
    }
}
