<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterCustomerHonorificTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class MasterCustomerHonorificTitleTest
 * @package Tests\Models
 */
class MasterCustomerHonorificTitleTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var MasterCustomerHonorificTitle
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterCustomerHonorificTitle
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
        $model = factory(MasterCustomer::class, 10)->create();
        // デフォルトデータ保持
        $default_data = DB::table('m_customers_honorific_titles')
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new MasterCustomerHonorificTitle();
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
            /** 得意先ID */
            'customer_id',
            /** 敬称ID */
            'honorific_title_id',
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
