<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterConstrSite;
use App\Models\Master\MasterCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class MasterConstrSiteTest
 * @package Tests\Models
 */
class MasterConstrSiteTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterConstrSite
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterConstrSite
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
        $model = factory(MasterConstrSite::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new MasterConstrSite())->getTable();  // テーブル名取得
        $default_data = DB::table($table)
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterConstrSite();
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
            /** 工事ID */
            'id',
            /** 工事コード */
            'code',
            /** 工事番号 */
            'construction_site_number',
            /** 得意先ID */
            'customer_id',
            /** 工事名 */
            'name',
            /** 住所 */
            'address',
            /** 契約日 */
            'contract_date',
            /** 契約金額 */
            'contract_amount',
            /** 着工日 */
            'start_date',
            /** 完成日 */
            'completion_date',
            /** 部門ID */
            'warehouse_id',
            /** 担当者ID */
            'employee_id',
            /** 工事種別ID */
            'construction_site_type_id',
            /** 請負業者タイプ */
            'contractor_type',
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
     */
    public function test_mCustomer()
    {
        $customer = factory(MasterCustomer::class)->create();
        $constr_site = factory(MasterConstrSite::class)->create([
            'customer_id' => $customer->id,
        ]);

        // 得意先と工事マスターの得意先IDとで確認（取得確認）
        $expected = $customer->name;
        $actual = $constr_site->mCustomer->name;
        $this->assertSame($expected, $actual);
    }

    #endregion mCustomer() Test

    #region getCustomerNameAttribute() Test

    /**
     * @test
     */
    public function test_getCustomerNameAttribute()
    {
        $default_data = $this->default_data[4];
        $data = MasterConstrSite::where('id', $default_data['id'])->first();
        $table_customer = with(new MasterCustomer())->getTable();  // テーブル名取得
        $customer = DB::table($table_customer)
            ->where('id', $default_data['customer_id'])
            ->first();

        $expected = $customer->name;
        $actual = $data->customer_name;
        $this->assertSame($expected, $actual);
    }

    #endregion getCustomerNameAttribute() Test
}
