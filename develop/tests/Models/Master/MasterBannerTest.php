<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterBanner;
use App\Models\Master\MasterBannerSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterBannerTest
 * @package Tests\Models
 */
class MasterBannerTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterBanner
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterBanner
     */
    protected $defaultData = [];

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
        $model = factory(MasterBanner::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new MasterBanner())->getTable();
        $default_data = DB::table($table)
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterBanner();
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
