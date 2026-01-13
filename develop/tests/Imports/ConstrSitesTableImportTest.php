<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests\Imports;

use App\Imports\ConstrSitesTableImport;
use App\Models\Master\MasterConstrSite;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConstrSitesTableImportTest extends TestCase
{
    /**
     * RefreshDatabaseトレイト
     * ※「migrate:refresh」１回実行
     */
    use RefreshDatabase;

    /**
     * @var ConstrSitesTableImport
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

        // DatabaseSeeder 実行
        $this->seed();

        // テストターゲット インスタンス化
        $this->target = new ConstrSitesTableImport();
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

    #region collection() Test

    /**
     * @test
     */
    public function test_collection_blank()
    {
        // ブランクチェック
        $rows = collect([]);

        $this->target->collection($rows);

        $table = with(new MasterConstrSite())->getTable();  // テーブル名取得
        $result = DB::table($table)
            ->get()
            ->toArray();

        $expected = [];
        $actual = $result;
        $this->assertSame($expected, $actual);
    }

    #endregion collection() Test

    #region checkRowData() Test

    /**
     * @test
     */
    public function test_checkRowData_blank()
    {
        // ブランクチェック
        $rows = collect([]);

        $expected = null;
        $actual = $this->target->checkRowData($rows);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_checkRowData_ConstrSiteNumber_Duplicate()
    {
        // 工事番号重複ありチェック
        $code = '000000100';
        $constr_site_number = '100';
        $model = factory(MasterConstrSite::class)->create([
        ]);
        $model->code = $code;
        $model->construction_site_number = $constr_site_number;
        $model->save();

        $rows = collect([
            0 => collect([
                0 => '000000101',
                1 => $constr_site_number,
            ]),
        ]);

        $expected = "Excelデータ内に既に登録済みの工事番号[{$constr_site_number}]があります。";
        $actual = $this->target->checkRowData($rows);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_checkRowData_ConstrSiteNumber_Duplicate_non_code()
    {
        // 工事番号重複ありチェック、コード指定なし
        $code = 100;
        $constr_site_number = '100';
        $model = factory(MasterConstrSite::class)->create([
        ]);
        $model->code = $code;
        $model->construction_site_number = $constr_site_number;
        $model->save();

        $rows = collect([
            0 => collect([
                0 => null,
                1 => $constr_site_number,
            ]),
        ]);

        $expected = "Excelデータ内に既に登録済みの工事番号[{$constr_site_number}]があります。";
        $actual = $this->target->checkRowData($rows);
        $this->assertSame($expected, $actual);
    }

    #endregion checkRowData() Test

    #region getChangeData() Test

    /**
     * @test
     */
    public function test_getChangeData_null()
    {
        // value nullチェック
        $value = null;

        $expected = null;
        $actual = $this->target->getChangeData($value);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getChangeData_numeric()
    {

        $this->markTestIncomplete(
            'このテストは、まだ実装されていません。'
        );

        // value 数値チェック
        $value = 44197;     // 「2021/01/01」

        $expected = Carbon::parse('2021/01/01');
        $actual = $this->target->getChangeData($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function test_getChangeData_string()
    {
        // value 文字列チェック
        $value = '2021/01/01';

        $expected = Carbon::parse('2021/01/01');
        $actual = $this->target->getChangeData($value);
        $this->assertEquals($expected, $actual);
    }

    #endregion getChangeData() Test
}
