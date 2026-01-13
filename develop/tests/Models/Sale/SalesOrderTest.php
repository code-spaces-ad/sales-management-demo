<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Sale;

use App\Models\Master\MasterCustomer;
use App\Models\Sale\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class SalesOrderTest
 * @package Tests\Models\Sale
 */
class SalesOrderTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var SalesOrder
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
        $model = factory(SalesOrder::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new SalesOrder())->getTable();  // テーブル名取得
        $default_data = DB::table($table)->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new SalesOrder();
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
            /** 伝票番号 */
            'order_number',
            /** 伝票日付 */
            'order_date',
            /** 請求日 */
            'billing_date',
            /** 得意先ID */
            'customer_id',
            /** 工事ID */
            'construction_site_id',
            /** 取引種別ID */
            'transaction_type_id',
            /** 売上合計 */
            'sales_total',
            /** 値引 */
            'discount',
            /** 備考 */
            'note',
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

    #region mCustomer() Test

    /**
     * @test
     * @see SalesOrder::mCustomer
     */
    public function test_mCustomer()
    {
        // ※factoryで、得意先のデータもセットしている前提
        $sales_order = factory(SalesOrder::class)->create();
        $customer = $sales_order->mCustomer;

        // 得意先IDと得意先リレーションの得意先IDとで確認（取得確認）
        $expected = $sales_order->customer_id;
        $actual = $customer->id;
        $this->assertSame($expected, $actual);
    }

    #endregion mCustomer() Test

    #region mConstrSite() Test

    /**
     * @test
     * @see SalesOrder::mConstrSite
     */
    public function test_mConstrSite()
    {
        // ※factoryで、工事マスターのデータもセットしている前提
        $sales_order = factory(SalesOrder::class)->create();
        $constr_site = $sales_order->mConstrSite;

        // 工事IDと工事リレーションの工事IDとで確認（取得確認）
        $expected = $sales_order->construction_site_id;
        $actual = $constr_site->id;
        $this->assertSame($expected, $actual);
    }

    #endregion mConstrSite() Test

    #region mTransactionType() Test

    /**
     * @test
     * @see SalesOrder::mTransactionType
     */
    public function test_mTransactionType()
    {
        // ※seederで、取引種別マスターのデータもセットしている前提
        $sales_order = factory(SalesOrder::class)->create();
        $transaction_type = $sales_order->mTransactionType;

        // 取引種別IDと取引種別リレーションの取引種別IDとで確認（取得確認）
        $expected = $sales_order->transaction_type_id;
        $actual = $transaction_type->id;
        $this->assertSame($expected, $actual);
    }

    #endregion mTransactionType() Test

    #region getOrderNumberZerofillAttribute() Test

    /**
     * @test
     * @see SalesOrder::getOrderNumberZerofillAttribute
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

    #region getCustomerNameAttribute() Test

    /**
     * @test
     * @see SalesOrder::getCustomerNameAttribute
     */
    public function test_getCustomerNameAttribute()
    {
        $default_data = $this->default_data[0];
        $data = $this->target->find($default_data['id']);

        $table_customer = with(new MasterCustomer())->getTable();  // テーブル名取得
        $customer = DB::table($table_customer)->find($default_data['customer_id']);

        $expected = $customer->name;
        $actual = $data->customer_name;
        $this->assertSame($expected, $actual);
    }

    #endregion getCustomerNameAttribute() Test
}
