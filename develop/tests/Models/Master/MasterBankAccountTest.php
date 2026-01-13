<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterBankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class MasterBankAccountTest
 * @package Tests\Models
 */
class MasterBankAccountTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterBankAccount
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterBankAccount
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

        // DatabaseSeeder 実行
        $this->seed();

        // デフォルトデータセット
        $model = factory(MasterBankAccount::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new MasterBankAccount())->getTable();
        $default_data = DB::table($table)->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterBankAccount();
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
            /** 銀行口座ID */
            'id',
            /** 銀行口座コード */
            'code',
            /** 金融機関名 */
            'bank_name',
            /** 支店名 */
            'branch_name',
            /** 預金種目 */
            'deposit_type',
            /** 口座番号 */
            'account_number',
            /** 口座名 */
            'account_name',
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

    #region scopeBankName() Test

    /**
     * @test
     */
    public function test_scopeBankName()
    {
        $default_data = $this->default_data[1];
        $data = $this->target
            ->bankName($default_data['bank_name'])
            ->first()
            ->toArray();

        $expected = $default_data;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    #endregion scopeBankName() Test

    #region scopeBranchName() Test

    /**
     * @test
     */
    public function test_scopeBranchName()
    {
        $default_data = $this->default_data[2];
        $data = $this->target
            ->branchName($default_data['branch_name'])
            ->first()
            ->toArray();

        $expected = $default_data;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    #endregion scopeBranchName() Test
}
