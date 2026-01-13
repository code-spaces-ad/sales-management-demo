<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Config\Consts;

use Tests\TestCase;

/**
 * Class DefaultTest
 * @package Tests\Config\Consts
 */
class DefaultTest extends TestCase
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

    #region sales_order.product_row_count

    /**
     * @test
     */
    public function test_sales_order_product_row_count()
    {
        $expected = 30;
        $actual   = config('consts.default.sales_order.product_row_count');
        $this->assertSame($expected, $actual);
    }

    #endregion sales_order.product_row_count
}
