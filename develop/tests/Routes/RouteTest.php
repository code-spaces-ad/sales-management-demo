<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Routes;

use App\Enums\UserRoleType;
use App\Models\Master\MasterUser;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class RouteTest
 * @package Tests\Routes
 */
class RouteTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * ログイン用ユーザー
     *
     * @var MasterUser
     */
    protected $user = null;

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

        // ログイン用ユーザー生成
        $this->user = factory(MasterUser::class)->create([
            'role_id' => UserRoleType::SYS_ADMIN,
        ]);
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
    public function testGuest_ルートページ()
    {
//        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();

        // ルートにアクセス
        $response = $this->get(RouteServiceProvider::HOME);

        // ログイン画面へリダイレクト
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function testGuest_存在するページ_ユーザーマスター()
    {
        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();

        // ユーザーマスター一覧画面（存在するページ）にアクセス
        $response = $this->get('/system/users');

        // ログイン画面へリダイレクト
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function testGuest_存在するページ_404エラーページ()
    {
//        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();

        // 404エラー画面（存在するページ）にアクセス
        $response = $this->get('/error/404');

        // ログイン画面へリダイレクト
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function testGuest_存在しないページ()
    {
        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();

        // 存在しないページにアクセス
        $response = $this->get('/xxxx');

        // ログイン画面へリダイレクト
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function testUser_ルートページ()
    {
//        $this->withoutExceptionHandling();

        // 認証済みにする（ログイン済み）
        $this->actingAs($this->user);

        // ルートにアクセス
        $response = $this->get(RouteServiceProvider::HOME);

        // ユーザーマスター画面へリダイレクト
        $response->assertRedirect('/system/users');
    }

    /**
     * @test
     */
    public function testUser_存在するページ_404エラーページ()
    {
//        $this->withoutExceptionHandling();

        // 認証済みにする（ログイン済み）
        $this->actingAs($this->user);

        // 404エラー画面（存在するページ）にアクセス
        $response = $this->get('/error/404');

        // ステータス確認 ※ページはあるが404が返る
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function testUser_存在するページ_ユーザーマスター()
    {
//        $this->withoutExceptionHandling();

        // 認証済みにする（ログイン済み）
        $this->actingAs($this->user);

        // ユーザーマスター一覧画面（存在するページ）にアクセス
        $response = $this->get('/system/users');

        // ステータス確認
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function testUser_存在しないページ()
    {
//        $this->withoutExceptionHandling();

        // 認証済みにする（ログイン済み）
        $this->actingAs($this->user);

        // 存在しないページにアクセス
        $response = $this->get('/xxxx');

        // ステータス確認
        $response->assertStatus(404);
    }
}
