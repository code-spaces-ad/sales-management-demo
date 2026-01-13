<?php

namespace Tests\Http\Controllers\Master;

use Tests\Http\Controllers\ControllerTestBase;

/**
 * Class MasterBizPartnerControllerTest
 * @package Tests\Http\Controllers\Auth
 * @see MasterSupplierController
 */
class MasterBizPartnerControllerTest extends ControllerTestBase
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
    public function testMasterSupplier_未ログイン確認()
    {
        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();

        // 一覧画面にアクセス
        $response = $this->get('/master/biz_partners');

        // ログイン後にホーム画面にリダイレクト確認
        $response->assertStatus(302)
            ->assertRedirect('/login'); // リダイレクト先を確認

        // 認証されていないことを確認
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function testMasterSupplier_indexアクション()
    {
        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();
        // 認証済みにする（ログイン済み）
        $this->actingAs($this->user);
        // 認証を確認
        $this->assertAuthenticated();

        // 一覧画面にアクセス
        $response = $this->get('/master/biz_partners');

        // 表示確認
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function testMasterSupplier_createアクション()
    {
        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();
        // 認証済みにする（ログイン済み）
        $this->actingAs($this->user);
        // 認証を確認
        $this->assertAuthenticated();

        // 登録画面にアクセス
        $response = $this->get('/master/biz_partners/create');

        // 表示確認
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function testMasterSupplier_editアクション()
    {
        $this->markTestIncomplete(
            'このテストは、まだ実装されていません。'
        );

        $this->withoutExceptionHandling();

        // 認証されていないことを確認
        $this->assertGuest();
        // 認証済みにする（ログイン済み）
        $this->actingAs($this->user);
        // 認証を確認
        $this->assertAuthenticated();

        // 編集画面にアクセス
        $response = $this->get('/master/biz_partners/1/edit');

        // 表示確認
        $response->assertStatus(200);
    }
}
