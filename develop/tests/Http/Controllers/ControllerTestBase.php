<?php

namespace Tests\Http\Controllers;

use App\Models\Master\MasterUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class ControllerTestBase
 * @package Tests\Http\Controllers\Auth
 */
class ControllerTestBase extends TestCase
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
        $this->user = factory(MasterUser::class)->create();
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
}
