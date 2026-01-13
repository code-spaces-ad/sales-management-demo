<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Consts\DB\Master\MasterAccountingCodesConst;
use App\Consts\DB\Master\MasterCategoriesConst;
use App\Consts\DB\Master\MasterClassifications1Const;
use App\Consts\DB\Master\MasterClassifications2Const;
use App\Consts\DB\Master\MasterCustomerPriceConst;
use App\Consts\DB\Master\MasterCustomersConst;
use App\Consts\DB\Master\MasterDepartmentsConst;
use App\Consts\DB\Master\MasterEmployeesConst;
use App\Consts\DB\Master\MasterKindsConst;
use App\Consts\DB\Master\MasterOfficeFacilitiesConst;
use App\Consts\DB\Master\MasterProductsConst;
use App\Consts\DB\Master\MasterSectionsConst;
use App\Consts\DB\Master\MasterSubCategoriesConst;
use App\Consts\DB\Master\MasterSummaryGroupConst;
use App\Consts\DB\Master\MasterSuppliersConst;
use App\Consts\DB\Master\MasterUsersConst;
use App\Consts\DB\Master\MasterWarehousesConst;
use App\Models\Master\MasterAccountingCode;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterClassification1;
use App\Models\Master\MasterClassification2;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterCustomerPrice;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterKind;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterSection;
use App\Models\Master\MasterSubCategory;
use App\Models\Master\MasterSummaryGroup;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUser;
use App\Models\Master\MasterWarehouse;
use Illuminate\Support\Facades\DB;

/**
 * コードヘルパークラス
 */
class CodeHelper
{
    /***
     * @param string $table_name
     * @param int $current_code
     * @return string
     */
    public static function getNextUsableCode(string $table_name, int $current_code): string
    {
        $arr_select_column_main = [DB::raw('min(A.id) as code')];
        // 対象テーブルの件数を取得
        $record_count = DB::table($table_name)->count();
        // 対象テーブルの id の最小値を取得
        $record_min_id = DB::table($table_name)->min('code');
        // 入力値と最小idを比較し、大きい数値を採番の開始番号とする
        if ($record_min_id < $current_code) {
            $record_min_id = $current_code;
        }

        $result = DB::table('z_code_list', 'A')
            ->select($arr_select_column_main)
            ->whereRaw('A.id between :current_code1 and :current_code2 and A.id not in (SELECT code FROM ' . $table_name . ') and A.id >= :current_code3',
                ['current_code1' => $record_min_id, 'current_code2' => $record_min_id + $record_count, 'current_code3' => $current_code])
            ->value('code');

        $return_code = is_null($result) ? $current_code + 1 : $result;
        $len = 8;
        if ($table_name === 'm_customers') {
            $len = MasterCustomersConst::CODE_MAX_LENGTH;
        }
        if ($table_name === 'm_suppliers') {
            $len = MasterSuppliersConst::CODE_MAX_LENGTH;
        }
        if ($table_name === 'm_products') {
            $len = MasterProductsConst::CODE_MAX_LENGTH;
        }
        if ($table_name === 'm_employees') {
            $len = MasterEmployeesConst::CODE_MAX_LENGTH;
        }
        if ($table_name === 'm_warehouses') {
            $len = MasterWarehousesConst::CODE_MAX_LENGTH;
        }
        if ($table_name === 'm_users') {
            $len = MasterUsersConst::CODE_MAX_LENGTH;
        }
        if ($table_name === 'm_categories') {
            $len = MasterCategoriesConst::CODE_MAX_LENGTH;
        }

        if ($table_name === 'm_customer_price') {
            $len = MasterCustomerPriceConst::CODE_MAX_LENGTH;
        }

        return sprintf("%0{$len}d", $return_code);
    }

    /***
     * @param string $table_name
     * @param int $current_code
     * @return string
     */
    public static function getNextUsableSortCode(string $table_name, int $current_code): string
    {
        $arr_select_column_main = [DB::raw('min(A.id) as code')];

        $result = DB::table('z_code_list', 'A')
            ->select($arr_select_column_main)
            ->whereRaw('A.id between :current_code1 and :current_code2 and A.id not in (SELECT sort_code FROM ' . $table_name . ') and A.id >= :current_code3',
                ['current_code1' => $current_code, 'current_code2' => $current_code + 10000, 'current_code3' => $current_code])
            ->value('code');

        return is_null($result) ? $current_code + 1 : $result;
    }

    public static function getAvailableNumber(?array $code_list, ?int $available_number): string
    {
        if (empty($code_list)) {
            $code_list = [];
        }

        // 文字列を数値として扱う
        $code_list = array_filter(array_map('intval', $code_list), function ($val) {
            return is_int($val) && $val >= 0;
        });

        // ソート
        sort($code_list);

        if ($available_number === null) {
            $available_number = empty($code_list) ? 1 : max($code_list) + 1;
        }

        // 空き番号検索
        foreach ($code_list as $code) {
            if ($code < $available_number) {
                continue;
            }
            if ($code === $available_number) {
                ++$available_number;
            } else {
                break;
            }
        }

        return $available_number;
    }

    /**
     * 使用中のコードリストとコード最大桁長を返す
     *
     * @param string $type
     * @param string|null $parent_key
     * @param string|null $parent_id
     * @return array
     */
    public static function getCodeList(string $type, ?string $parent_key, ?string $parent_id): array
    {
        [$model, $const] = self::getModel($type);
        $data = $model::query()->withTrashed();
        if (isset($parent_key) && isset($parent_id)) {
            $data = $data->where($parent_key, $parent_id);
        }

        return [
            $const::CODE_MAX_LENGTH,
            count($data->pluck('code')->toArray()) !== 0 ? $data->pluck('code')->toArray() : [0 => '0'],
        ];
    }

    /**
     * 使用中のソートリストとソート最大桁長を返す
     *
     * @param string $type
     * @return array
     */
    public static function getSortList(string $type): array
    {
        [$model, $const] = self::getModel($type);

        return [
            $model::query()->withTrashed()->pluck('sort_code')->count() !== 0 ?
                $model::query()->withTrashed()->pluck('sort_code')->toArray() : [0 => '0'],
        ];
    }

    /**
     * type指定のmodelとconstを返す
     *
     * @param string $type
     * @return string[]
     */
    private static function getModel(string $type): array
    {
        if ($type === 'accounting_codes') {
            $model = MasterAccountingCode::class;
            $const = MasterAccountingCodesConst::class;
        }
        if ($type === 'categories') {
            $model = MasterCategory::class;
            $const = MasterCategoriesConst::class;
        }
        if ($type === 'classifications1') {
            $model = MasterClassification1::class;
            $const = MasterClassifications1Const::class;
        }
        if ($type === 'classifications2') {
            $model = MasterClassification2::class;
            $const = MasterClassifications2Const::class;
        }
        if ($type === 'customers') {
            $model = MasterCustomer::class;
            $const = MasterCustomersConst::class;
        }
        if ($type === 'departments') {
            $model = MasterDepartment::class;
            $const = MasterDepartmentsConst::class;
        }
        if ($type === 'employees') {
            $model = MasterEmployee::class;
            $const = MasterEmployeesConst::class;
        }
        if ($type === 'kinds') {
            $model = MasterKind::class;
            $const = MasterKindsConst::class;
        }
        if ($type === 'office_facilities') {
            $model = MasterOfficeFacility::class;
            $const = MasterOfficeFacilitiesConst::class;
        }
        if ($type === 'products') {
            $model = MasterProduct::class;
            $const = MasterProductsConst::class;
        }
        if ($type === 'sections') {
            $model = MasterSection::class;
            $const = MasterSectionsConst::class;
        }
        if ($type === 'sub_categories') {
            $model = MasterSubCategory::class;
            $const = MasterSubCategoriesConst::class;
        }
        if ($type === 'suppliers') {
            $model = MasterSupplier::class;
            $const = MasterSuppliersConst::class;
        }
        if ($type === 'warehouses') {
            $model = MasterWarehouse::class;
            $const = MasterWarehousesConst::class;
        }
        if ($type === 'summary_group') {
            $model = MasterSummaryGroup::class;
            $const = MasterSummaryGroupConst::class;
        }
        if ($type === 'users') {
            $model = MasterUser::class;
            $const = MasterUsersConst::class;
        }
        if ($type === 'customer_price') {
            $model = MasterCustomerPrice::class;
            $const = MasterCustomerPriceConst::class;
        }

        return [$model, $const];
    }
}
