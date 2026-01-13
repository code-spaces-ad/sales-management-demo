<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Enums\UserRoleType;
use App\Models\Master\MasterUser;
use App\Models\Master\MasterUserSupplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class MasterUserTest
 * @package Tests\Models
 */
class MasterUserTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterUser
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterUsersTableSeeder
     */
    protected $default_data = [
        [
            'id' => 1,
            'code' => 1,
            'login_id' => 'sysadmin',   // ※UNIQUE
            'password' => 'sysadmin',
            'name' => 'システム管理者',
            'role_id' => UserRoleType::SYS_ADMIN,
            'email' => 'sysadmin@test.com',
            'note' => 'テスト用アカウント\r\nパスワード：create7706\r\n',
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

        // テストターゲット インスタンス化
        $this->target = new MasterUser();

        // パスワード変換（Hash値に変更）
        for ($i = 0; $i < count($this->default_data); $i++) {
            $this->default_data[$i]['password'] = Hash::make($this->default_data[$i]['password']);
        }

        // デフォルトデータ追加
        DB::table($this->target->getTable())->insert(
            $this->default_data
        );
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
            ->select('id', 'code', 'login_id', 'password', 'name', 'role_id', 'email', 'note')
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
            /** ユーザーID */
            'id',
            /** コード */
            'code',
            /** ログインID */
            'login_id',
            /** パスワード */
            'password',
            /** 名前 */
            'name',
            /** 権限 */
            'role_id',
            /** メールアドレス */
            'email',
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

    #region getIdZerofillAttribute() Test

    /**
     * @test
     */
    public function test_getIdZerofillAttribute_Normal()
    {
        $default_data = $this->default_data[0];
        $id_zerofill = sprintf("%010d", $default_data['id']);
        $data = $this->target
            ->where('id', $default_data['id'])
            ->first();

        $expected = $id_zerofill;
        $actual = $data->id_zerofill;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getIdZerofillAttribute_IDがNULL()
    {
        $data = new MasterUser();

        $expected = null;
        $actual = $data->id_zerofill;
        $this->assertSame($expected, $actual);
    }

    #endregion getIdZerofillAttribute() Test

    #region getUserSupplierIdAttribute() Test

    /**
     * @test
     */
    public function test_getUserSupplierIdAttribute()
    {
        // ※ユーザー作成ファクトリで、ユーザー_業者リレーションも追加される前提
        $model = factory(MasterUser::class)->create([
            'role_id' => UserRoleType::SUPPLIER,
        ]);

        $usr_biz_partner = DB::table(with(new MasterUserSupplier())->getTable())
            ->where('user_id', $model->id)
            ->first();

        $expected = $usr_biz_partner->supplier_id;
        $actual = $model->user_biz_partner_id;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getUserSupplierIdAttribute_SUPPLIER以外()
    {
        // ※ユーザー作成ファクトリで、ユーザー_業者リレーションが追加されない
        $model = factory(MasterUser::class)->create([
            'role_id' => UserRoleType::ACCOUNTANT,
        ]);

//        $usr_biz_partner = DB::table(with(new MasterUserSupplier())->getTable())
//            ->where('user_id', $model->id)
//            ->first();

        $expected = null;
        $actual = $model->user_biz_partner_id;
        $this->assertSame($expected, $actual);
    }

    #endregion getUserSupplierIdAttribute() Test
}
