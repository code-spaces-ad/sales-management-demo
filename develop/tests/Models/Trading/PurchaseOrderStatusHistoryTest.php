<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Trading;

use App\Models\Trading\PurchaseOrder;
use App\Models\Trading\PurchaseOrderStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class PurchaseOrderStatusHistoryTest
 * @package Tests\Models\Trading
 */
class PurchaseOrderStatusHistoryTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var PurchaseOrderStatusHistory
     */
    protected $target;

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

        // デフォルトデータセット（発注伝票でセット）
        $model = factory(PurchaseOrder::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new PurchaseOrderStatusHistory())->getTable();  // テーブル名取得
        $default_data = DB::table($table)->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new PurchaseOrderStatusHistory();
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
            /** ID */
            'id',
            /** 発注伝票ID */
            'purchase_order_id',
            /** 状態 */
            'order_status',
            /** 更新者ID */
            'updated_id',
            /** 作成日時 */
            'created_at',
        ];
        $actual = array_keys($data[0]);     // ※一つ目のデータでチェック
        $this->assertSame($expected, $actual);
    }
}
