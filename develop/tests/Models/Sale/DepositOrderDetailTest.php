<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Sale;

use App\Models\Sale\DepositOrder;
use App\Models\Sale\DepositOrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class SalesOrderDetailTest
 * @package Tests\Models\Sale
 */
class DepositOrderDetailTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var DepositOrderDetail
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see DepositOrderDetail
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
        $default_data = DB::table('deposit_order_details')->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new DepositOrderDetail();
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
            /** 入金伝票ID */
            'deposit_order_id',
            /** 金額_現金 */
            'amount_cash',
            /** 金額_小切手 */
            'amount_check',
            /** 金額_振込 */
            'amount_transfer',
            /** 金額_手形 */
            'amount_bill',
            /** 金額_相殺 */
            'amount_offset',
            /** 金額_値引 */
            'amount_discount',
            /** 金額_手数料 */
            'amount_fee',
            /** 金額_その他 */
            'amount_other',
            /** 備考_現金 */
            'note_cash',
            /** 備考_小切手 */
            'note_check',
            /** 備考_振込 */
            'note_transfer',
            /** 備考_手形 */
            'note_bill',
            /** 備考_相殺 */
            'note_offset',
            /** 備考_値引 */
            'note_discount',
            /** 備考_手数料 */
            'note_fee',
            /** 備考_その他 */
            'note_other',
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
