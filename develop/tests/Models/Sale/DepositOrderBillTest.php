<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Sale;

use App\Models\Sale\DepositOrder;
use App\Models\Sale\DepositOrderBill;
use App\Models\Sale\DepositOrderDetail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class DepositOrderBillTest
 * @package Tests\Models\Sale
 */
class DepositOrderBillTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var DepositOrderBill
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see DepositOrderBill
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
        $model = factory(DepositOrder::class, 10)->create();
        // デフォルトデータ保持
        $default_data = DB::table('deposit_order_bill')
            ->get();

        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new DepositOrderBill();
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
        $this->markTestIncomplete(
            'このテストは、まだ実装されていません。'
        );

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
            /** 入金伝票D */
            'deposit_order_id',
            /** 手形期日 */
            'bill_date',
            /** 手形番号 */
            'bill_number',
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
