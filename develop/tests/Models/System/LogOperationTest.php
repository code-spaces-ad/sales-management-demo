<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\System;

use App\Models\System\LogOperation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class LogOperationTest
 * @package Tests\Models\System
 */
class LogOperationTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var LogOperation
     */
    protected $target;

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
        $model = factory(LogOperation::class, 10)->create();
        // デフォルトデータ保持
        $table = with(new LogOperation())->getTable();
        $default_data = DB::table($table)
            ->get();
        // ※stdClassから、arrayに変更
        $this->default_data = json_decode(json_encode($default_data), true);

        // テストターゲット インスタンス化
        $this->target = new LogOperation();
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
            /** ID */
            'id',
            /** ユーザーID */
            'user_id',
            /** ルート名 */
            'route_name',
            /** 要求パス */
            'request_url',
            /** 要求メソッド */
            'request_method',
            /** HTTPステータスコード */
            'status_code',
            /** 要求内容 */
            'request_message',
            /** クライアントIPアドレス */
            'remote_addr',
            /** ブラウザ名 */
            'user_agent',
            /** 作成日時 */
            'created_at',
        ];
        $actual = array_keys($data[0]);     // ※一つ目のデータでチェック
        $this->assertSame($expected, $actual);
    }

    #region getOutOfTermId() Test

    /**
     * @test
     */
    public function test_getOutOfTermId_データなし()
    {
        // ※ログデータは、実行時の当日分しかない前提
        $target_date = Carbon::yesterday();
        $data = $this->target->getOutOfTermId($target_date);

        $expected = null;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getOutOfTermId_データあり()
    {
        $model = factory(LogOperation::class)->create([
            /** 作成日時 */
            'created_at' => Carbon::now()->addMonths(-1),   // 1か月前に設定
        ]);
        $target_date = Carbon::yesterday();
        $data = $this->target->getOutOfTermId($target_date);

        $expected = $model->id;
        $actual = $data;
        $this->assertSame($expected, $actual);
    }

    #endregion getOutOfTermId() Test
}
