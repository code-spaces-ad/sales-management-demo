<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Enums\UserRoleType;
use App\Models\Master\MasterRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class MasterRoleTest
 * @package Tests\Models\Master
 */
class MasterRoleTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterRole
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see \MasterUnitsTableSeeder
     */
    protected $defaultData = [
        [
            /** 権限ID */
            'id' => UserRoleType::SYS_ADMIN,
            /** 権限名 */
            'name' => 'システム管理者',
        ],
        [
            /** 権限ID */
            'id' => UserRoleType::SYS_OPERATOR,
            /** 権限名 */
            'name' => 'システム運用者',
        ],
        [
            /** 権限ID */
            'id' => UserRoleType::OWNER,
            /** 権限名 */
            'name' => 'オーナー',
        ],
        [
            /** 権限ID */
            'id' => UserRoleType::ACCOUNTANT,
            /** 権限名 */
            'name' => '経理担当者',
        ],
        [
            /** 権限ID */
            'id' => UserRoleType::MANAGER,
            /** 権限名 */
            'name' => '現場管理者',
        ],
        [
            /** 権限ID */
            'id' => UserRoleType::EMPLOYEE,
            /** 権限名 */
            'name' => '従業員',
        ],
        [
            /** 権限ID */
            'id' => UserRoleType::SUPPLIER,
            /** 権限名 */
            'name' => '取引業者',
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
        $this->target = new MasterRole();
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
