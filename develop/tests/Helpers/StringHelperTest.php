<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Helpers;

use App\Helpers\StringHelper;
use Tests\TestCase;
use TypeError;

/**
 * Class StringHelperTest
 * @package Tests\Helpers
 */
class StringHelperTest extends TestCase
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
    public function testGetNameWithId_IdとNameがNULL()
    {
        $this->expectException(TypeError::class);

        $id = null;
        $name = null;

        $expected = ":";
        $actual = StringHelper::getNameWithId($id, $name);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testGetNameWithId_IdとNameあり()
    {
        $id = 5;    // ※intでも暗黙変換で通る
        $name = 'XXXX';

        $expected = "{$id}: {$name}";
        $actual = StringHelper::getNameWithId($id, $name);
        $this->assertSame($expected, $actual);
    }
}
