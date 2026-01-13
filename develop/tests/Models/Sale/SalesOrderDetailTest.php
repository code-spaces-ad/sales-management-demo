<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Sale;

use App\Models\Master\MasterProduct;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Sale\SalesOrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class SalesOrderDetailTest
 * @package Tests\Models\Sale
 */
class SalesOrderDetailTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var SalesOrderDetail
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see SalesOrderDetail
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
        $model = factory(SalesOrderDetail::class, 10)->create();
        // デフォルトデータ保持
        $default_data = DB::table('sales_order_details')->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new SalesOrderDetail();
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
            /** 売上伝票ID */
            'sales_order_id',
            /** 商品ID */
            'product_id',
            /** 商品名 */
            'product_name',
            /** 数量 */
            'quantity',
            /** 単価 */
            'unit_price',
            /** 消費税率 */
            'consumption_tax_rate',
            /** 軽減税率対象フラグ */
            'reduced_tax_flag',
            /** 消費税端数処理方法 */
            'rounding_method_id',
            /** 部門ID */
            'warehouse_id',
            /** 備考 */
            'note',
            /** ソート */
            'sort',
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

    #region getRoundingMethodNameAttribute() Test

    /**
     * @test
     */
    public function test_getRoundingMethodNameAttribute()
    {
        $default_data = $this->default_data[2];
        $rounding_method_name = MasterRoundingMethod::find($default_data['rounding_method_id'])->name;
        $data = $this->target
            ->where('sales_order_id', $default_data['sales_order_id'])
            ->first();

        $expected = $rounding_method_name;
        $actual = $data->rounding_method_name;
        $this->assertSame($expected, $actual);
    }

    #endregion getRoundingMethodNameAttribute() Test

    #region getQuantityDigitCutAttribute() Test

    /**
     * @test
     */
    public function test_getQuantityDigitCutAttribute()
    {
        $this->markTestIncomplete(
            'このテストは、まだ実装されていません。'
        );

        $quantity_decimal_digit = 2;
        $product = factory(MasterProduct::class)->create([
            'quantity_decimal_digit' => $quantity_decimal_digit,
        ]);

        // ※指定の桁数に数値があっても切り捨て
        $quantity = '1234.12';
        $order_detail = factory(SalesOrderDetail::class)->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);

        $expected = '1234.12';
        $actual = $order_detail->quantity_digit_cut;
        $this->assertSame($expected, $actual);
    }

    #endregion getQuantityDigitCutAttribute() Test

    #region getUnitPriceDigitCutAttribute() Test

    /**
     * @test
     */
    public function test_getUnitPriceDigitCutAttribute()
    {
        $this->markTestIncomplete(
            'このテストは、まだ実装されていません。'
        );

        $unit_price_decimal_digit = 2;
        $product = factory(MasterProduct::class)->create([
            'unit_price_decimal_digit' => $unit_price_decimal_digit,
        ]);

        // ※指定の桁数に数値があっても切り捨て
        $unit_price = '1234.1';
        $order_detail = factory(SalesOrderDetail::class)->create([
            'product_id' => $product->id,
            'unit_price' => $unit_price,
        ]);

        $expected = '1234.00';
        $actual = $order_detail->unit_price_digit_cut;
        $this->assertSame($expected, $actual);
    }

    #endregion getUnitPriceDigitCutAttribute() Test
}
