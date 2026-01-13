<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Sale;

use App\Models\Invoice\ChargeData;
use App\Models\Sale\SalesOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class ChargeDataTest
 * @package Tests\Models\Sale
 */
class ChargeDataTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var ChargeData
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see SalesOrder
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
        $model = factory(ChargeData::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new ChargeData())->getTable();  // テーブル名取得
        $default_data = DB::table($table)->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new ChargeData();
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

        foreach ($actual as $key => $detail) {
            // charge_start_date, charge_end_date を差し替え（テスト的に微妙だが）
            $str = $detail['charge_start_date'];
            $actual[$key]['charge_start_date'] = substr($str, 0, strcspn($str, ' '));
            $str = $detail['charge_end_date'];
            $actual[$key]['charge_end_date'] = substr($str, 0, strcspn($str, ' '));
        }

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
            /** 請求開始日 */
            'charge_start_date',
            /** 請求終了日 */
            'charge_end_date',
            /** 得意先ID */
            'customer_id',
            /** 前回請求額 */
            'before_charge_total',
            /** 今回入金額 */
            'payment_total',
            /** 調整額 */
            'adjust_amount',
            /** 繰越残高 */
            'carryover',
            /** 今回売上額 */
            'sales_total',
            /** 今回売上額_通常税率分 */
            'sales_total_normal',
            /** 今回売上額_軽減税率分 */
            'sales_total_reduced',
            /** 今回売上額_非課税 */
            'sales_total_free',
            /** 値引調整額 */
            'discount_total',
            /** 消費税額 */
            'sales_tax_total',
            /** 消費税額_通常税率分 */
            'sales_tax_total_normal',
            /** 消費税率_軽減税率分 */
            'sales_tax_total_reduced',
            /** 今回請求額 */
            'charge_total',
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

    #region getChargeData() Test

    /**
     * @test
     */
    public function test_getChargeData_通常()
    {
        $customer_id = $this->default_data[0]['customer_id'];
        $charge_date = $this->default_data[0]['charge_end_date'];

        $data = $this->target->getChargeData($customer_id, $charge_date);

        $expected = $this->default_data[0];
        $actual = $data->toArray();
        // charge_start_date, charge_end_date を差し替え（テスト的に微妙だが）
        $str = $actual['charge_start_date'];
        $actual['charge_start_date'] = substr($str, 0, strcspn($str, ' '));
        $str = $actual['charge_end_date'];
        $actual['charge_end_date'] = substr($str, 0, strcspn($str, ' '));

        $this->assertEquals($expected, $actual);    // ※assertEquals でチェック
    }

    /**
     * @test
     */
    public function test_getChargeData_Customerなし()
    {
        $customer_id = null;
        $charge_date = $this->default_data[0]['charge_end_date'];

        $data = $this->target->getChargeData($customer_id, $charge_date);

        $class = get_class($this->target);

        $expected = new $class();
        $actual = $data;
        $this->assertEquals($expected, $actual);    // ※assertEquals でチェック
    }

    /**
     * @test
     */
    public function test_getChargeData_請求日なし()
    {
        $customer_id = $this->default_data[0]['customer_id'];
        $charge_date = '';

        $data = $this->target->getChargeData($customer_id, $charge_date);

        $class = get_class($this->target);

        $expected = new $class();
        $actual = $data;
        $this->assertEquals($expected, $actual);    // ※assertEquals でチェック
    }

    #endregion getChargeData() Test
}
