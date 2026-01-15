<?php

use App\Models\Master\MasterAccountingCode;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterClassification2;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET foreign_key_checks = 0');

        MasterDepartment::query()->truncate();
        // MasterOfficeFacility::query()->truncate();
        // MasterEmployee::query()->truncate();
        MasterAccountingCode::query()->truncate();
        MasterCategory::query()->truncate();
        MasterSubCategory::query()->truncate();
        MasterClassification2::query()->truncate();

        // 部門マスタ
        $table_name = 'm_departments';
        $file_path = database_path('seeds/csv/m_departments.csv');
        DB::transaction(function () use ($table_name, $file_path) {
            if (($handle = fopen($file_path, 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    DB::table($table_name)->insert([
                        'code' => $data['code'],
                        'name' => $data['name'],
                        'name_kana' => $data['name_kana'],
                        'mnemonic_name' => $data['mnemonic_name'],
                        'note' => $data['note'],
                    ]);
                }
                fclose($handle);
            }
        });

        // 事業所マスタ
        // $table_name = 'm_office_facilities';
        // $file_path = database_path('seeds/csv/m_office_facilities.csv');
        // DB::transaction(function () use ($table_name, $file_path) {
        //     if (($handle = fopen($file_path, 'r')) !== false) {
        //         $header = fgetcsv($handle);
        //         while (($row = fgetcsv($handle)) !== false) {
        //             $data = array_combine($header, $row);
        //             DB::table($table_name)->insert([
        //                 'code' => $data['code'],
        //                 'department_id' => $data['department_id'],
        //                 'name' => $data['name'],
        //                 'manager_id' => (strlen($data['manager_id']) >0) ? $data['manager_id'] : null,
        //                 'note' => $data['note'],
        //             ]);
        //         }
        //         fclose($handle);
        //     }
        // });

        // 社員マスタ
        // $table_name = 'm_employees';
        // $file_path = database_path('seeds/csv/m_employees.csv');
        // DB::transaction(function () use ($table_name, $file_path) {
        //     if (($handle = fopen($file_path, 'r')) !== false) {
        //         $header = fgetcsv($handle);
        //         while (($row = fgetcsv($handle)) !== false) {
        //             $data = array_combine($header, $row);
        //             DB::table($table_name)->insert([
        //                 'code' => $data['code'],
        //                 'name' => $data['name'],
        //                 'name_kana' => $data['name_kana'],
        //                 'department_id' => (strlen($data['department_id']) >0) ? $data['department_id'] : null,
        //                 'office_facilities_id' => (strlen($data['office_facilities_id']) >0) ? $data['office_facilities_id'] : null,
        //                 'note' => $data['note'],
        //             ]);
        //         }
        //         fclose($handle);
        //     }
        // });

        // 経理マスタ
        $table_name = 'm_accounting_codes';
        $file_path = database_path('seeds/csv/m_accounting_codes.csv');
        DB::transaction(function () use ($table_name, $file_path) {
            if (($handle = fopen($file_path, 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    DB::table($table_name)->insert([
                        'code' => $data['code'],
                        'name' => $data['name'],
                        'note' => $data['note'],
                        'output_group' => $data['output_group'],
                    ]);
                }
                fclose($handle);
            }
        });

        // カテゴリマスタ
        $table_name = 'm_categories';
        $file_path = database_path('seeds/csv/m_categories.csv');
        DB::transaction(function () use ($table_name, $file_path) {
            if (($handle = fopen($file_path, 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    DB::table($table_name)->insert([
                        'code' => $data['code'],
                        'name' => $data['name'],
                    ]);
                }
                fclose($handle);
            }
        });

        // サブカテゴリマスタ
        $table_name = 'm_sub_categories';
        $file_path = database_path('seeds/csv/m_sub_categories.csv');
        DB::transaction(function () use ($table_name, $file_path) {
            if (($handle = fopen($file_path, 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    DB::table($table_name)->insert([
                        'code' => $data['code'],
                        'category_id' => $data['category_id'],
                        'name' => $data['name'],
                    ]);
                }
                fclose($handle);
            }
        });

        // 分類2マスタ
        $table_name = 'm_classifications2';
        $file_path = database_path('seeds/csv/m_classifications2.csv');
        DB::transaction(function () use ($table_name, $file_path) {
            if (($handle = fopen($file_path, 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    DB::table($table_name)->insert([
                        'code' => $data['code'],
                        'name' => $data['name'],
                    ]);
                }
                fclose($handle);
            }
        });

        DB::statement('SET foreign_key_checks = 1');
    }
}
