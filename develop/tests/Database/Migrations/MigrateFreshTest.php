<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Database\Migrations;

use Tests\TestCase;

/**
 * Class MigrateFreshTest
 * @package Tests\Database\Migrations
 */
class MigrateFreshTest extends TestCase
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
    public function test_migrate_fresh()
    {
        // 「php artisan migrate:fresh」コマンド確認
        $this->artisan('migrate:fresh');
        $this->assertTrue(true);
    }
}
