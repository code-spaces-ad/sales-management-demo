<?php

namespace Tests\Http\Controllers\Auth;

use App\Models\Master\MasterUser;
use Illuminate\Support\Facades\Hash;
use Tests\Http\Controllers\ControllerTestBase;

/**
 * Class LoginControllerTest
 * @package Tests\Http\Controllers\Auth
 * @see LoginController
 */
class LoginControllerTest extends ControllerTestBase
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
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
    public function testLogin_ログイン画面表示()
    {
        $this->withoutExceptionHandling();

        // ログイン画面にアクセス
        $response = $this->get('/login');

        $response->assertStatus(200);
        // 認証されていないことを確認
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function testLogin_未ログイン確認()
    {
        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();

        // 認証前に発注一覧画面にアクセス
        $response = $this->get('/trading/ordering');

        $response->assertStatus(302)
            ->assertRedirect('/login'); // リダイレクト先を確認

        // 認証されていないことを確認
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function testLogin_ログイン確認()
    {
        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();

        // ログインユーザー生成
        $password = 'test1111';
        $user = factory(MasterUser::class)->create([
            'password' => Hash::make($password)
        ]);

        // ログイン実行
        $response = $this->post('/login', [
            'login_id' => $user->login_id,
            'password' => $password
        ]);

        // 認証を確認
        $this->assertAuthenticated();

        // ログイン後にホーム画面にリダイレクト
        $response->assertStatus(302)
            ->assertRedirect('/'); // リダイレクト先を確認
    }

    /**
     * @test
     */
    public function testLogin_ログアウト確認()
    {
        $this->withoutExceptionHandling();

        // ログインユーザー生成
        $user = factory(MasterUser::class)->create();
        // 認証済みにする（ログイン済み）
        $this->actingAs($user);
        // 認証を確認
        $this->assertAuthenticated();

        // ログアウト実行
        $response = $this->post('/logout');

        // ホーム画面にリダイレクト
        $response->assertStatus(302)
            ->assertRedirect('/'); // リダイレクト先を確認

        // 認証されていないことを確認
        $this->assertGuest();
    }
}
