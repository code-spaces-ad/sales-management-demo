<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterSupplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class MasterSupplierTest
 * @package Tests\Models
 */
class MasterSupplierTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterSupplier
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
        $model = factory(MasterSupplier::class, 10)->create();

        // デフォルトデータ保持
        $table = with(new MasterSupplier())->getTable();
        $default_data = DB::table($table)
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterSupplier();
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
            /** 業者ID */
            'id',
            /** 業者コード */
            'code',
            /** 業者名 */
            'name',
            /** 業者名カナ */
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

    #region scopeNameKana() Test

    /**
     * @test
     */
    public function testScopeNameKana()
    {
        $biz_partner = $this->default_data[0];
        $data = $this->target
            ->nameKana($biz_partner['name_kana'])
            ->first()
            ->toArray();

        $expected = $biz_partner;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    #endregion scopeNameKana() Test
}
