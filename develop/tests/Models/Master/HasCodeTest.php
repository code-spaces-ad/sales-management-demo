<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\HasCode;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/**
 * Class HasCodeTest
 * @package Tests\Models
 */
class HasCodeTest extends TestCase
{
    /**
     * @var object
     */
    protected $target;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // テストターゲット インスタンス化
        $this->target = new class extends Model {
            use HasCode;
        }; // 無名クラス
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
    public function test_HasCode使用可確認()
    {
        $expected = 'object';
        $actual = gettype($this->target);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getCodeColumn()
    {
        $expected = 'code';
        $actual = $this->target->getCodeColumn();
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_scopeCode()
    {
        // ※「scopeCode」メソッドがあるかで判定（処理は、各モデルで確認する）
        $expected = true;
        $actual = in_array('scopeCode', get_class_methods($this->target));
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getCodeZerofillAttribute()
    {
        // ※「getCodeZerofillAttribute」メソッドがあるかで判定（処理は、各モデルで確認する）
        $expected = true;
        $actual = in_array('getCodeZerofillAttribute', get_class_methods($this->target));
        $this->assertSame($expected, $actual);
    }
}
