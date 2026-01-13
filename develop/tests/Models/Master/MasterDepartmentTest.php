<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterWarehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterWarehouseTest
 * @package Tests\Models
 */
class MasterWarehouseTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterWarehouse
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterWarehouse
     */
    protected $default_data = [
        [
            /** 部門ID */
            'id' => 1,
            /** 部門コード */
            'code' => 1,
            /** 部門名 */
            'name' => '総務部',
            /** 部門名カナ */
            'name_kana' => 'ソウムブ',
        ],
        [
            /** 部門ID */
            'id' => 2,
            /** 部門コード */
            'code' => 2,
            /** 部門名 */
            'name' => '解体部',
            /** 部門名カナ */
            'name_kana' => 'カイタイブ',
        ],
        [
            /** 部門ID */
            'id' => 3,
            /** 部門コード */
            'code' => 3,
            /** 部門名 */
            'name' => '土木部',
            /** 部門名カナ */
            'name_kana' => 'ドボクブ',
        ],
    ];

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

        // テストターゲット インスタンス化
        $this->target = new MasterWarehouse();
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
            ->select('id', 'code', 'name', 'name_kana')
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
            'created_at',
            'updated_at',
            'deleted_at'
        ];
        $actual = array_keys($data[0]);     // ※一つ目のデータでチェック
        $this->assertSame($expected, $actual);
    }
}
