<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 商品マスターテーブル（m_products） Seeder Class
 */
class MasterProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_products')->insert(
            [
                [
                    /** ID */
                    'id' => 150101,
                    /** コード */
                    'code' => 150101,
                    /** 名前 */
                    'name' => 'テスト商品1',
                    /** カテゴリーID */
                    'category_id' => 15,
                    /** 単価 */
                    'unit_price' => 500,
                    /** 仕入単価 */
                    'purchase_unit_price' => 400,
                    /** 税区分 */
                    'tax_type_id' => 1,
                    /** 軽減税率対象フラグ */
                    'reduced_tax_flag' => 0,
                    /** 数量端数処理 */
                    'quantity_rounding_method_id' => 3,
                    /** 金額端数処理 */
                    'amount_rounding_method_id' => 3,
                ],
                [
                    /** ID */
                    'id' => 150111,
                    /** コード */
                    'code' => 150111,
                    /** 名前 */
                    'name' => 'テスト商品2',
                    /** カテゴリーID */
                    'category_id' => 15,
                    /** 単価 */
                    'unit_price' => 500,
                    /** 仕入単価 */
                    'purchase_unit_price' => 350,
                    /** 税区分 */
                    'tax_type_id' => 1,
                    /** 軽減税率対象フラグ */
                    'reduced_tax_flag' => 1,
                    /** 数量端数処理 */
                    'quantity_rounding_method_id' => 3,
                    /** 金額端数処理 */
                    'amount_rounding_method_id' => 2,
                ],
                [
                    /** ID */
                    'id' => 150209,
                    /** コード */
                    'code' => 150209,
                    /** 名前 */
                    'name' => 'テスト商品3',
                    /** カテゴリーID */
                    'category_id' => 15,
                    /** 単価 */
                    'unit_price' => 700,
                    /** 仕入単価 */
                    'purchase_unit_price' => 550,
                    /** 税区分 */
                    'tax_type_id' => 2,
                    /** 軽減税率対象フラグ */
                    'reduced_tax_flag' => 0,
                    /** 数量端数処理 */
                    'quantity_rounding_method_id' => 3,
                    /** 金額端数処理 */
                    'amount_rounding_method_id' => 2,
                ],
                [
                    /** ID */
                    'id' => 150310,
                    /** コード */
                    'code' => 150310,
                    /** 名前 */
                    'name' => 'テスト商品4',
                    /** カテゴリーID */
                    'category_id' => 15,
                    /** 単価 */
                    'unit_price' => 1000,
                    /** 仕入単価 */
                    'purchase_unit_price' => 850,
                    /** 税区分 */
                    'tax_type_id' => 2,
                    /** 軽減税率対象フラグ */
                    'reduced_tax_flag' => 1,
                    /** 数量端数処理 */
                    'quantity_rounding_method_id' => 3,
                    /** 金額端数処理 */
                    'amount_rounding_method_id' => 1,
                ],
                [
                    /** ID */
                    'id' => 151436,
                    /** コード */
                    'code' => 151436,
                    /** 名前 */
                    'name' => 'テスト商品5',
                    /** カテゴリーID */
                    'category_id' => 1,
                    /** 単価 */
                    'unit_price' => 600,
                    /** 仕入単価 */
                    'purchase_unit_price' => 450,
                    /** 税区分 */
                    'tax_type_id' => 3,
                    /** 軽減税率対象フラグ */
                    'reduced_tax_flag' => 0,
                    /** 数量端数処理 */
                    'quantity_rounding_method_id' => 3,
                    /** 金額端数処理 */
                    'amount_rounding_method_id' => 3,
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
