<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // 権限マスターテーブル デフォルトセット
            MasterRolesTableSeeder::class,
            // ユーザーマスターテーブル デフォルトセット
            MasterUsersTableSeeder::class,
            // 本社情報マスターテーブル デフォルトセット
            MasterHeadOfficeInfoTableSeeder::class,
            // 単位マスターテーブル デフォルトセット
            MasterUnitsTableSeeder::class,
            // 端数処理マスターテーブル デフォルトセット
            MasterRoundingMethodsTableSeeder::class,
            // 敬称マスターテーブル デフォルトセット
            MasterHonorificTitlesTableSeeder::class,
            // 消費税マスターテーブル デフォルトセット
            MasterConsumptionTaxesTableSeeder::class,
            // 取引種別マスターテーブル デフォルトセット
            MasterTransactionTypesTableSeeder::class,
            // 倉庫マスターテーブル デフォルトセット
            MasterWarehousesTableSeeder::class,
            // 社員マスターテーブル デフォルトセット
            MasterEmployeesTableSeeder::class,
            // 部門マスタテーブル デフォルトセット
//            MasterDepartmentsTableSeeder::class,
            // 事業所マスターテーブル デフォルトセット
           MasterOfficeFacilitiesTableSeeder::class,
            // 経理コードマスターテーブル デフォルトセット
//            MasterAccountingCodesTableSeeder::class,
            // 集計グループマスターテーブル デフォルトセット
            MasterSummaryGroupTableSeeder::class,
            // 得意先マスターテーブル デフォルトセット
            MasterCustomersTableSeeder::class,
            // 仕入先マスターテーブル デフォルトセット
            MasterSuppliersTableSeeder::class,
            // カテゴリーマスターテーブル デフォルトセット
//            MasterCategoriesTableSeeder::class,
            // サブカテゴリーマスターテーブル デフォルトセット
//            MasterSubCategoriesTableSeeder::class,
            // 商品マスターテーブル デフォルトセット
            MasterProductsTableSeeder::class,
            // 商品_単位リレーションテーブル デフォルトセット
            MasterProductsUnitsTableSeeder::class,
            // 種別マスターテーブル デフォルトセット
            MasterKindsTableSeeder::class,
            // 管理部署マスターテーブル デフォルトセット
            MasterSectionsTableSeeder::class,
            // 分類1マスターテーブル デフォルトセット
            MasterClassifications1TableSeeder::class,
            // 分類2マスターテーブル デフォルトセット
//            MasterClassifications2TableSeeder::class,

            CsvSeeder::class,

            // 設定テーブル デフォルトセット
            SettingsSeeder::class,

            // テストデータセット
            TestDataSeeder::class,
        ]);
    }
}
