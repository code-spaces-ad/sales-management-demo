<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterBannerSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterBannerSizeTest
 * @package Tests\Models
 */
class MasterBannerSizeTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterBannerSize
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see \MasterBannerTableSeeder
     */
    protected $defaultData = [
        [
            /** ID */
            'id' => 1,
            /** サイズ名 */
            'name' => '大',
            /** CSS幅 */
            'width' => 300,
            /** CSS高さ */
            'height' => 600,
        ],
        [
            /** ID */
            'id' => 2,
            /** サイズ名 */
            'name' => '中',
            /** CSS幅 */
            'width' => 300,
            /** CSS高さ */
            'height' => 300,
        ],
        [
            /** ID */
            'id' => 3,
            /** サイズ名 */
            'name' => '小',
            /** CSS幅 */
            'width' => 300,
            /** CSS高さ */
            'height' => 150,
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
        $this->target = new MasterBannerSize();
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
            ->select(
               /** バナーサイズID */
                'id',
                /** バナーサイズ名 */
                'name',
                /** CSS幅 */
                'width',
                /** CSS高さ */
                'height'
            )
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
            /** バナーサイズID */
            'id',
            /** バナーサイズ名 */
            'name',
            /** CSS幅 */
            'width',
            /** CSS高さ */
            'height',
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
}
