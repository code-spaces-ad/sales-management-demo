<?php

/**
 * seeder/faker設定
 *
 * @copyright © 2025 レボルシオン株式会社
 */

return [
    /**
     * 得意先マスタ
     *   単独実施コマンド：php artisan db:seed --class=MasterCustomersTableSeeder
     */
    'customer' => [
        /** 生成件数 */
        'generate_count' => 1000,
        /** truncate ※基本的にrefresh/fresh時は消えているので単体実施時用 */
        'truncate' => false,
    ],
    /**
     * 支所マスタ
     *   単独実施コマンド：php artisan db:seed --class=MasterBranchesTableSeeder
     */
    'branch' => [
        /** 生成件数 */
        'generate_count' => 100,
        /** truncate ※基本的にrefresh/fresh時は消えているので単体実施時用 */
        'truncate' => false,
    ],
    /**
     * 納品先マスタ
     *   単独実施コマンド：php artisan db:seed --class=MasterRecipientsTableSeeder
     */
    'recipient' => [
        /** 生成件数 */
        'generate_count' => 500,
        /** truncate ※基本的にrefresh/fresh時は消えているので単体実施時用 */
        'truncate' => false,
    ],
    /**
     * 仕入先マスタ
     *   単独実施コマンド：php artisan db:seed --class=MasterSuppliersTableSeeder
     */
    'supplier' => [
        /** 生成件数 */
        'generate_count' => 100,
        /** truncate ※基本的にrefresh/fresh時は消えているので単体実施時用 */
        'truncate' => false,
    ],
    /**
     * 社員マスタ
     *   単独実施コマンド：php artisan db:seed --class=MasterEmployeesTableSeeder
     */
    'employee' => [
        /** 生成件数 */
        'generate_count' => 10,
        /** truncate ※基本的にrefresh/fresh時は消えているので単体実施時用 */
        'truncate' => false,
    ],
    /**
     * 商品マスタ
     *   単独実施コマンド：php artisan db:seed --class=MasterProductsTableSeeder
     */
    'products' => [
        /** 生成件数 */
        'generate_count' => 50,
        /** truncate ※基本的にrefresh/fresh時は消えているので単体実施時用 */
        'truncate' => false,
    ],
];
