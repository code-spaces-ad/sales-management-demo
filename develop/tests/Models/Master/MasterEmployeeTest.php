<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterEmployee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class MasterEmployeeTest
 * @package Tests\Models
 */
class MasterEmployeeTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterEmployee
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterEmployee
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

        // デフォルトデータセット
        $model = factory(MasterEmployee::class, 10)->create();
        // デフォルトデータ保持
        $default_data = DB::table('m_employees')
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterEmployee();
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
//            ->select('id', 'code', 'name', 'name_kana', 'note')
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
            'id',
            'code',
            'name',
            'name_kana',
            'birthday',
            'hire_date',
            'note',
            'created_at',
            'updated_at',
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
//            ->select('id', 'code', 'name', 'name_kana')
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
//            ->select('id', 'code', 'name', 'name_kana')
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
//            ->select('id', 'code', 'name', 'name_kana')
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
//            ->select('id', 'code', 'name', 'name_kana')
            ->nameKana($default_data['name_kana'])
            ->first()
            ->toArray();

        $expected = $default_data;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    #endregion scopeNameKana() Test
}
