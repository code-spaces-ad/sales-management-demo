<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Enums;

use App\Enums\DepositType;
use Tests\TestCase;

/**
 * Class DepositTypeTest
 * @package Tests\Enums
 * @see DepositType
 */
class DepositTypeTest extends TestCase
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
    public function test_SAVINGS_ACCOUNT()
    {
        $expected = 1;
        $actual = DepositType::SAVINGS_ACCOUNT;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_CHECKING_ACCOUNT()
    {
        $expected = 2;
        $actual = DepositType::CHECKING_ACCOUNT;
        $this->assertSame($expected, $actual);
    }
}
