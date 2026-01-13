<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Helpers;

use App\Helpers\TaxHelper;
use Tests\TestCase;
use TypeError;

/**
 * Class TaxHelperTest
 * @package Tests\Helpers
 */
class TaxHelperTest extends TestCase
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

    #region getIncTax() Test

    /**
     * @test
     */
    public function test_getIncTax_RoundDown()
    {
        // 切り捨てパターン
        $price = 105;
        $tax_rate = 10;
        $rounding_method_id = 1;

        $expected = 115;
        $actual = TaxHelper::getIncTax($price, $tax_rate, $rounding_method_id);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getIncTax_RoundUp()
    {
        // 切り上げパターン
        $price = 105;
        $tax_rate = 10;
        $rounding_method_id = 2;

        $expected = 116;
        $actual = TaxHelper::getIncTax($price, $tax_rate, $rounding_method_id);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getIncTax_RoundOff()
    {
        // 四捨五入パターン
        $price = 104;
        $tax_rate = 10;
        $rounding_method_id = 3;

        $expected = 114;
        $actual = TaxHelper::getIncTax($price, $tax_rate, $rounding_method_id);
        $this->assertEquals($expected, $actual);
    }

    #endregion getIncTax() Test
}
