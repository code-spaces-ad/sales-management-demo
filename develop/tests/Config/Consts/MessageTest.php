<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Config\Consts;

use Tests\TestCase;

/**
 * Class MessageTest
 * @package Tests\Config\Consts
 */
class MessageTest extends TestCase
{
    /**
     * setUp
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * tearDown
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    #region common

    /**
     * @test
     */
    public function test_common_store_success()
    {
        $expected = '登録に成功しました。';
        $actual   = config('consts.message.common.store_success');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_common_store_failed()
    {
        $expected = '登録に失敗しました。';
        $actual   = config('consts.message.common.store_failed');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_common_update_success()
    {
        $expected = '編集に成功しました。';
        $actual   = config('consts.message.common.update_success');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_common_update_failed()
    {
        $expected = '編集に失敗しました。';
        $actual   = config('consts.message.common.update_failed');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_common_destroy_success()
    {
        $expected = '削除に成功しました。';
        $actual   = config('consts.message.common.destroy_success');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_common_destroy_failed()
    {
        $expected = '削除に失敗しました。';
        $actual   = config('consts.message.common.destroy_failed');
        $this->assertSame($expected, $actual);
    }

    #endregion common

    #region confirm

    /**
     * @test
     */
    public function test_confirm_store()
    {
        $expected = '登録します。よろしいですか？';
        $actual   = config('consts.message.common.confirm.store');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_confirm_delete()
    {
        $expected = '削除します。よろしいですか？';
        $actual   = config('consts.message.common.confirm.delete');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_confirm_copy()
    {
        $expected = 'コピーします。よろしいですか？';
        $actual   = config('consts.message.common.confirm.copy');
        $this->assertSame($expected, $actual);
    }

    #endregion confirm
}
