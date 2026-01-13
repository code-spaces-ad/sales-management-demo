<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterUnitTest
 * @package Tests\Models
 */
class MasterUnitTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterUnit
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see \MasterUnitsTableSeeder
     */
    protected $defaultData = [
        [
            /** 単位ID */
            'id' => 1,
            /** 単位コード */
            'code' => 1,
            /** 単位名 */
            'name' => 'ｔ',
        ],
        [
            /** 単位ID */
            'id' => 2,
            /** 単位コード */
            'code' => 2,
            /** 単位名 */
            'name' => 'ｍ3',
        ],
        [
            /** 単位ID */
            'id' => 3,
            /** 単位コード */
            'code' => 3,
            /** 単位名 */
            'name' => '台',
        ],
        [
            /** 単位ID */
            'id' => 4,
            /** 単位コード */
            'code' => 4,
            /** 単位名 */
            'name' => '式',
        ],
        [
            /** 単位ID */
            'id' => 5,
            /** 単位コード */
            'code' => 5,
            /** 単位名 */
            'name' => 'ｍ2',
        ],
        [
            /** 単位ID */
            'id' => 6,
            /** 単位コード */
            'code' => 6,
            /** 単位名 */
            'name' => '本',
        ],
        [
            /** 単位ID */
            'id' => 7,
            /** 単位コード */
            'code' => 7,
            /** 単位名 */
            'name' => 'ｍ',
        ],
        [
            /** 単位ID */
            'id' => 8,
            /** 単位コード */
            'code' => 8,
            /** 単位名 */
            'name' => 'H',
        ],
        [
            /** 単位ID */
            'id' => 9,
            /** 単位コード */
            'code' => 9,
            /** 単位名 */
            'name' => '枚',
        ],
        [
            /** 単位ID */
            'id' => 10,
            /** 単位コード */
            'code' => 10,
            /** 単位名 */
            'name' => '㍑',
        ],
        [
            /** 単位ID */
            'id' => 11,
            /** 単位コード */
            'code' => 11,
            /** 単位名 */
            'name' => '人',
        ],
        [
            /** 単位ID */
            'id' => 12,
            /** 単位コード */
            'code' => 12,
            /** 単位名 */
            'name' => '日',
        ],
        [
            /** 単位ID */
            'id' => 13,
            /** 単位コード */
            'code' => 13,
            /** 単位名 */
            'name' => '回',
        ],
        [
            /** 単位ID */
            'id' => 14,
            /** 単位コード */
            'code' => 14,
            /** 単位名 */
            'name' => '月',
        ],
        [
            /** 単位ID */
            'id' => 15,
            /** 単位コード */
            'code' => 15,
            /** 単位名 */
            'name' => '箱',
        ],
        [
            /** 単位ID */
            'id' => 16,
            /** 単位コード */
            'code' => 16,
            /** 単位名 */
            'name' => '％',
        ],
        [
            /** 単位ID */
            'id' => 17,
            /** 単位コード */
            'code' => 17,
            /** 単位名 */
            'name' => '個',
        ],
        [
            /** 単位ID */
            'id' => 18,
            /** 単位コード */
            'code' => 18,
            /** 単位名 */
            'name' => '名',
        ],
        [
            /** 単位ID */
            'id' => 19,
            /** 単位コード */
            'code' => 19,
            /** 単位名 */
            'name' => 'ヵ月',
        ],
        [
            /** 単位ID */
            'id' => 20,
            /** 単位コード */
            'code' => 20,
            /** 単位名 */
            'name' => '袋',
        ],
        [
            /** 単位ID */
            'id' => 21,
            /** 単位コード */
            'code' => 21,
            /** 単位名 */
            'name' => 'ヶ所',
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
        $this->target = new MasterUnit();
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
        $expected = $this->defaultData;
        $actual = $this->target
            ->select('id', 'code', 'name')
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
            'created_at',
            'updated_at',
            'deleted_at'
        ];
        $actual = array_keys($data[0]);     // ※一つ目のデータでチェック
        $this->assertSame($expected, $actual);
    }
}
