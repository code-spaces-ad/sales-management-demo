<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Trading;

use App\Models\Master\MasterSupplier;
use App\Models\Trading\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class PurchaseOrderTest
 * @package Tests\Models\Trading
 */
class PurchaseOrderTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var PurchaseOrder
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see PurchaseOrder
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
        $model = factory(PurchaseOrder::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new PurchaseOrder())->getTable();  // テーブル名取得
        $default_data = DB::table($table)->get();

        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new PurchaseOrder();
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
            // order_date を差し替え（テスト的に微妙だが）
            $str = $detail['order_date'];
            $actual[$key]['order_date'] = substr($str, 0, strcspn($str, ' '));
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
            /** 発注伝票ID */
            'id',
            /** 発注番号 */
            'order_number',
            /** 見積日付 */
            'estimate_date',
            /** 発注日付 */
            'order_date',
            /** 納品日付 */
            'delivery_date',
            /** 状態 */
            'order_status',
            /** 業者ID */
            'supplier_id',
            /** 工事ID */
            'construction_site_id',
            /** 担当者ID */
            'employee_id',
            /** 売上合計 */
            'sales_total',
            /** 値引 */
            'discount',
            /** 見積有効期限 */
            'estimate_validity_period',
            /** 備考 */
            'note',
            /** 更新者 */
            'updated_id',
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

    #region mSupplier() Test

    /**
     * @test
     * @see PurchaseOrder::mSupplier
     */
    public function test_mCustomer()
    {
        // ※factoryで、業者データもセットしている前提
        $purchase_order = factory(PurchaseOrder::class)->create();
        $biz_partner = $purchase_order->mSupplier;

        // 業者IDと業者リレーションの業者IDとで確認（取得確認）
        $expected = $purchase_order->supplier_id;
        $actual = $biz_partner->id;
        $this->assertSame($expected, $actual);
    }

    #endregion mSupplier() Test

    #region mConstrSite() Test

    /**
     * @test
     * @see PurchaseOrder::mConstrSite
     */
    public function test_mConstrSite()
    {
        // ※factoryで、工事マスターのデータもセットしている前提
        $sales_order = factory(PurchaseOrder::class)->create();
        $constr_site = $sales_order->mConstrSite;

        // 工事IDと工事リレーションの工事IDとで確認（取得確認）
        $expected = $sales_order->construction_site_id;
        $actual = $constr_site->id;
        $this->assertSame($expected, $actual);
    }

    #endregion mConstrSite() Test

    #region getOrderNumberZerofillAttribute() Test

    /**
     * @test
     * @see PurchaseOrder::getOrderNumberZerofillAttribute
     */
    public function test_getOrderNumberZerofillAttribute()
    {
        $default_data = $this->default_data[0];
        $order_number_zerofill = sprintf("%08d", $default_data['order_number']);
        $data = $this->target->find($default_data['id']);

        $expected = $order_number_zerofill;
        $actual = $data->order_number_zerofill;
        $this->assertSame($expected, $actual);
    }

    #endregion getOrderNumberZerofillAttribute() Test

    #region getSupplierNameAttribute() Test

    /**
     * @test
     * @see PurchaseOrder::getSupplierNameAttribute
     */
    public function test_getSupplierNameAttribute()
    {
        $default_data = $this->default_data[0];
        $data = $this->target->find($default_data['id']);

        $table_biz_partner = with(new MasterSupplier())->getTable();  // テーブル名取得
        $biz_partner = DB::table($table_biz_partner)->find($default_data['supplier_id']);

        $expected = $biz_partner->name;
        $actual = $data->biz_partner_name;
        $this->assertSame($expected, $actual);
    }

    #endregion getSupplierNameAttribute() Test

    #region getSupplierAddressAttribute() Test

    /**
     * @test
     * @see PurchaseOrder::getSupplierAddressAttribute
     */
    public function test_getSupplierAddressAttribute()
    {
        $default_data = $this->default_data[0];
        $data = $this->target->find($default_data['id']);

        $address1 = "宮崎県東臼杵郡門川町須賀崎";
        $address2 = "４丁目３９－３";

        $table_biz_partner = with(new MasterSupplier())->getTable();  // テーブル名取得
        $biz_partner = DB::table($table_biz_partner)
            ->where('id', $default_data['supplier_id'])
            ->update(
                [
                    'address1' => $address1,
                    'address2' => $address2,
                ]
            );

        $expected = $address1 . $address2;
        $actual = $data->biz_partner_address;
        $this->assertSame($expected, $actual);
    }

    #endregion getSupplierAddressAttribute() Test
}
