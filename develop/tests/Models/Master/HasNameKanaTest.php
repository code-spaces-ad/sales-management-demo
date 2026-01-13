<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\Master;

use App\Models\Master\HasNameKana;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/**
 * Class HasNameKanaTest
 * @package Tests\Models
 */
class HasNameKanaTest extends TestCase
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
            use HasNameKana;
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
    public function test_NameKana使用可確認()
    {
        $expected = 'object';
        $actual = gettype($this->target);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getNameKanaColumn()
    {
        $expected = 'name_kana';
        $actual = $this->target->getNameKanaColumn();
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_scopeNameKana()
    {
        // ※「scopeNameKana」メソッドがあるかで判定（処理は、各モデルで確認する）
        $expected = true;
        $actual = in_array('scopeNameKana', get_class_methods($this->target));
        $this->assertSame($expected, $actual);
    }
}
