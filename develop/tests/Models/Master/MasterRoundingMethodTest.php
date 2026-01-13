<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterRoundingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterRoundingMethodTest
 * @package Tests\Models
 */
class MasterRoundingMethodTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterRoundingMethod
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see \MasterUnitsTableSeeder
     */
    protected $defaultData = [
        [
            'id'   => 1,
            'name' => '切り捨て',
        ],
        [
            'id'   => 2,
            'name' => '切り上げ',
        ],
        [
            'id'   => 3,
            'name' => '四捨五入',
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
        $this->target = new MasterRoundingMethod();
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
            ->select('id', 'name')
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
            'name',
            'created_at',
            'updated_at',
            'deleted_at'
        ];
        $actual = array_keys($data[0]);     // ※一つ目のデータでチェック
        $this->assertSame($expected, $actual);
    }
}
