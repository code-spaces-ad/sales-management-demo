<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class MasterCustomerTest
 * @package Tests\Models
 */
class MasterCustomerTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterCustomer
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterCustomer
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
        $model = factory(MasterCustomer::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new MasterCustomer())->getTable();
        $default_data = DB::table($table)
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterCustomer();
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
            /** 得意先ID */
            'id',
            /** 得意先コード */
            'code',
            /** 得意先名 */
            'name',
            /** 得意先名カナ */
            'name_kana',
            /** 郵便番号1 */
            'postal_code1',
            /** 郵便番号2 */
            'postal_code2',
            /** 住所1 */
            'address1',
            /** 住所2 */
            'address2',
            /** 電話番号 */
            'tel_number',
            /** FAX番号 */
            'fax_number',
            /** メールアドレス */
            'email',
            /** 請求先ID */
            'billing_customer_id',
            /** 税額端数処理 */
            'tax_rounding_method_id',
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

    #region scopeSearchCondition() Test

    /**
     * @test
     */
    public function testScopeSearchCondition_id()
    {
        $default_data = $this->default_data[0];
        $search_condition_input_data = [
            'id' => $default_data['id'],
        ];

        $data = $this->target
            ->searchCondition($search_condition_input_data)
            ->first()
            ->toArray();

        $expected = $default_data;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testScopeSearchCondition_name()
    {
        $default_data = $this->default_data[0];
        $search_condition_input_data = [
            'name' => $default_data['name'],
        ];

        $data = $this->target
            ->searchCondition($search_condition_input_data)
            ->first()
            ->toArray();

        $expected = $default_data;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testScopeSearchCondition_nameKana()
    {
        $default_data = $this->default_data[0];
        $search_condition_input_data = [
            'name_kana' => $default_data['name_kana'],
        ];

        $data = $this->target
            ->searchCondition($search_condition_input_data)
            ->first()
            ->toArray();

        $expected = $default_data;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    #endregion scopeSearchCondition() Test

    #region scopeNameKana() Test

    /**
     * @test
     */
    public function testScopeNameKana()
    {
        $default_data = $this->default_data[0];
        $data = $this->target
            ->nameKana($default_data['name_kana'])
            ->first()
            ->toArray();

        $expected = $default_data;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    #endregion scopeNameKana() Test

    #region mCustomerHonorificTitle() Test

    /**
     * @test
     */
    public function testMCustomerHonorificTitle()
    {
        // ※factoryで、得意先敬称のデータもセットしている前提
        $model = factory(MasterCustomer::class)->create();
        $m_customer_honorific_title = $model->mCustomerHonorificTitle;

        // 得意先IDと得意先敬称の得意先IDとで確認（取得確認）
        $expected = $model->id;
        $actual = $m_customer_honorific_title->customer_id;
        $this->assertSame($expected, $actual);
    }

    #endregion mCustomerHonorificTitle() Test

    #region getCodeZerofillAttribute() Test

    /**
     * @test
     */
    public function test_getCodeZerofillAttribute()
    {
        $default_data = $this->default_data[0];
        $code_zerofill = sprintf("%08d", $default_data['code']);
        $data = $this->target
            ->where('id', $default_data['id'])
            ->first();

        $expected = $code_zerofill;
        $actual = $data->code_zerofill;
        $this->assertSame($expected, $actual);
    }

    #endregion getCodeZerofillAttribute() Test
}
