<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 消費税マスターテーブル（m_consumption_taxes） Seeder Class
 */
class MasterConsumptionTaxesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_consumption_taxes')->insert(
            [
                [
                    'id' => 1,
                    'begin_date' => '1989-04-01',
                    'normal_tax_rate' => 3,
                    'reduced_tax_rate' => null,
                ],
                [
                    'id' => 2,
                    'begin_date' => '1997-04-01',
                    'normal_tax_rate' => 5,
                    'reduced_tax_rate' => null,
                ],
                [
                    'id' => 3,
                    'begin_date' => '2014-04-01',
                    'normal_tax_rate' => 8,
                    'reduced_tax_rate' => null,
                ],
                [
                    'id' => 4,
                    'begin_date' => '2019-10-01',
                    'normal_tax_rate' => 10,
                    'reduced_tax_rate' => 8,
                ],
            ]
        );
    }
}
