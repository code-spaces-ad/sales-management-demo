<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterProduct;
use App\Models\Master\MasterUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class MasterProductTest
 * @package Tests\Models
 */
class MasterProductTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterProduct
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterProduct
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
        $model = factory(MasterProduct::class, 10)->create();
        // デフォルトデータ保持
        $default_data = DB::table('m_products')
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterProduct();
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
            /** 商品ID */
            'id',
            /** 商品コード */
            'code',
            /** 商品名 */
            'name',
            /** 商品名カナ */
            'name_kana',
            /** 単価 */
            'unit_price',
            /** 消費税率 */
            'consumption_tax_rate',
            /** 軽減税率対象フラグ */
            'reduced_tax_flag',
            /** 単価小数桁数 */
            'unit_price_decimal_digit',
            /** 数量小数桁数 */
            'quantity_decimal_digit',
            /** 数量端数処理 */
            'quantity_rounding_method_id',
            /** 金額端数処理 */
            'amount_rounding_method_id',
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

    #region mProductUnit() Test

    /**
     * @test
     */
    public function test_mProductUnit()
    {
        // ※factoryで、商品単位のデータもセットしている前提
        $product = factory(MasterProduct::class)->create();
        $m_product_unit = $product->mProductUnit;

        // 商品IDと商品単位の商品IDとで確認（取得確認）
        $expected = $product->id;
        $actual = $m_product_unit->product_id;
        $this->assertSame($expected, $actual);
    }

    #endregion mProductUnit() Test

    #region getCodeZerofillAttribute() Test

    /**
     * @test
     */
    public function test_getCodeZerofillAttribute()
    {
        $default_data = $this->default_data[0];
        $code_zerofill = sprintf("%04d", $default_data['code']);
        $data = $this->target
            ->where('id', $default_data['id'])
            ->first();

        $expected = $code_zerofill;
        $actual = $data->code_zerofill;
        $this->assertSame($expected, $actual);
    }

    #endregion getCodeZerofillAttribute() Test

    #region getProductUnitNameAttribute() Test

    /**
     * @test
     */
    public function test_getProductUnitNameAttribute()
    {
        $default_data = $this->default_data[4];
        $data = MasterProduct::where('id', $default_data['id'])->first();
        $table_unit = with(new MasterUnit())->getTable();  // テーブル名取得
        $unit = DB::table($table_unit)
            ->where('id', $data->mProductUnit->unit_id)
            ->first();

        $expected = $unit->name;
        $actual = $data->product_unit_name;
        $this->assertSame($expected, $actual);
    }

    #endregion getProductUnitNameAttribute() Test
}
