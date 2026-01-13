<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterTransactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterTransactionTypeTest
 * @package Tests\Models\Master
 */
class MasterTransactionTypeTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterTransactionType
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see \MasterTransactionTypesTableSeeder
     */
    protected $defaultData = [
        [
            /** 取引種別ID */
            'id' => 1,
            /** 取引種別名 */
            'name' => '現売',
        ],
        [
            /** 取引種別ID */
            'id' => 2,
            /** 取引種別名 */
            'name' => '掛売',
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
        $this->target = new MasterTransactionType();
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
               /** 取引種別ID */
                'id',
                /** 取引種別名 */
                'name'
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
            /** 取引種別ID */
            'id',
            /** 取引種別名 */
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
