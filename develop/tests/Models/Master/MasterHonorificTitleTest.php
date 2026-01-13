<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterHonorificTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterHonorificTitleTest
 * @package Tests\Models
 */
class MasterHonorificTitleTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterHonorificTitle
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see \MasterHonorificTitlesTableSeeder
     */
    protected $defaultData = [
        [
            /** 敬称ID */
            'id' => 1,
            /** 敬称コード */
            'code' => 1,
            /** 敬称名 */
            'name' => '御中',
        ],
        [
            /** 敬称ID */
            'id' => 2,
            /** 敬称コード */
            'code' => 2,
            /** 敬称名 */
            'name' => '様',
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
        $this->target = new MasterHonorificTitle();
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
            /** 敬称ID */
            'id',
            /** 敬称コード */
            'code',
            /** 敬称名 */
            'name',
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
