<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Models\System;

use App\Models\System\HeadOfficeInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class HeadOfficeInfoTest
 * @package Tests\Models\System
 */
class HeadOfficeInfoTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var HeadOfficeInfo
     */
    protected $target;

    /**
     * デフォルトのデータ配列
     * @var array
     * @see MasterHeadOfficeInfoTableSeeder
     */
    protected $default_data = [
        [
            'company_name'        => 'CodeSpaces',
            'representative_name' => '代表取締役　',
            'postal_code1'        => '885',
            'postal_code2'        => '0005',
            'address1'            => '宮崎県',
            'tel_number'          => '0986',
        ]
    ];

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

        // テストターゲット インスタンス化
        $this->target = new HeadOfficeInfo();
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
    public function testAll_データがあること()
    {
        $expected = $this->default_data;
        $actual = $this->target
            ->select(
                'company_name',
                'representative_name',
                'postal_code1',
                'postal_code2',
                'address1',
                'tel_number'
            )
            ->get()
            ->toArray();

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testAll_必要なフィールドがあること()
    {
        $data = $this->target->all()->toArray();

        $expected = [
            /** ID */
            'id',
            /** 会社名 */
            'company_name',
            /** 代表者名 */
            'representative_name',
            /** 郵便番号1 */
            'postal_code1',
            /** 郵便番号2 */
            'postal_code2',
            /** 住所1 */
            'address1',
            /** 住所2 */
            'address2',
            /** 電話番号 */
            'tel_number',
            /** FAX番号 */
            'fax_number',
            /** メールアドレス */
            'email',
            /** 社印画像 */
            'company_seal_image',
            /** 社印画像ファイル名 */
            'company_seal_image_file_name',
            /** インボイス登録番号 */
            'invoice_number',
            /** 作成日時 */
            'created_at',
            /** 更新日時 */
            'updated_at',
            /** 削除日時 */
            'deleted_at'
        ];
        $actual = array_keys($data[0]);     // ※一つ目のデータでチェック
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testAll_レコードがひとつだけ()
    {
        // ※本社情報は１レコードのみ
        $expected = 1;
        $actual = $this->target->all()->count();
        $this->assertSame($expected, $actual);
    }
}
