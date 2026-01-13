<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Enums;

use App\Enums\UserRoleType;
use Tests\TestCase;

/**
 * Class UserRoleTypeTest
 * @package Tests\Models
 */
class UserRoleTypeTest extends TestCase
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
    public function testSYS_ADMIN()
    {
        $expected = 1;
        $actual = UserRoleType::SYS_ADMIN;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testSYS_OPERATOR()
    {
        $expected = 2;
        $actual = UserRoleType::SYS_OPERATOR;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testOWNER()
    {
        $expected = 3;
        $actual = UserRoleType::OWNER;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testACCOUNTANT()
    {
        $expected = 4;
        $actual = UserRoleType::ACCOUNTANT;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testMANAGER()
    {
        $expected = 5;
        $actual = UserRoleType::MANAGER;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testEMPLOYEE()
    {
        $expected = 6;
        $actual = UserRoleType::EMPLOYEE;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testSUPPLIER()
    {
        $expected = 7;
        $actual = UserRoleType::SUPPLIER;
        $this->assertSame($expected, $actual);
    }
}
