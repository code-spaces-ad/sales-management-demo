<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Http\Middleware;

use App\Http\Middleware\LogOperationCreate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class LogOperationCreateTest
 * @package Tests\Http\Middleware
 */
class LogOperationCreateTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var LogOperationCreate
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

        // テストターゲット インスタンス化
        $this->target = new LogOperationCreate();
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

    #region operationLog() Test

    /**
     * @test
     */
    public function test_operationLog()
    {
        $this->markTestIncomplete(
            'このテストは、まだ実装されていません。'
        );

        $expected = true;
        $actual = false;
        $this->assertSame($expected, $actual);
    }

    #endregion operationLog() Test

    #region checkAcceptPattern() Test

    /**
     * @test
     * @dataProvider checkAcceptPatternProvider
     *
     * @param $route_name
     * @param $request_url
     * @param $user_id
     * @param $expected
     */
    public function test_checkAcceptPattern($route_name, $request_url, $user_id, $expected)
    {
        // ReflectionClass 作成
        $reflection = new \ReflectionClass($this->target);
        // メソッドを取得する
        $method = $reflection->getMethod('checkAcceptPattern');
        // アクセス許可をする
        $method->setAccessible(true);

        $actual = $method->invokeArgs($this->target, [$route_name, $request_url, $user_id]);
        $this->assertSame($expected, $actual);
    }

    /**
     * test_checkAcceptPattern 用dataProvider
     *
     * @return array
     */
    public function checkAcceptPatternProvider()
    {
        // ※log_operations.php の「accept_route_name」の設定に依存
        return [
            '01_login：ユーザーIDなし' => [null, 'login', null, false],
            '02_login：ユーザーIDあり' => [null, 'login', 1, true],
            '03_products_index' => ['master.products.index', 'master/products', 1, false],
            '04_products_store' => ['master.products.store', 'master/products', 1, true],
            '05_products_store：ユーザーIDなし' => ['master.products.store', 'master/products', null, true],
        ];
    }

    #endregion checkAcceptPattern() Test
}
