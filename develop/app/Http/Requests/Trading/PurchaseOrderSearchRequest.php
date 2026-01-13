<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Trading;

use App\Enums\OrderStatus;
use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterEmployee;
use App\Rules\EnumValueCustom;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 仕入一覧検索用 リクエストクラス
 */
class PurchaseOrderSearchRequest extends FormRequest
{
    use SearchRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $table_employee = with(new MasterEmployee())->getTable();
        $exists_employee = "exists:{$table_employee},id";

        $employee_id_validation_rule = ['bail', 'nullable', 'integer', $exists_employee];

        $order_status_list_validation_rule = ['bail', 'nullable', 'array'];
        $order_status_validation_rule = [
            'bail',
            'nullable',
            'integer',
            new EnumValueCustom(OrderStatus::class, false),
        ];

        $add_rules = [
            'employee_id' => $employee_id_validation_rule,
            'order_status' => $order_status_list_validation_rule,
            'order_status.*' => $order_status_validation_rule,
            'supplier_id' => ['bail', 'nullable', 'integer'],
            'department_id' => ['bail', 'nullable', 'integer'],
            'office_facility_id' => ['bail', 'nullable', 'integer'],
        ];

        // 共通化しているルール + $add_rulesに指定したルールをreturn
        return array_merge_recursive($this->setRulesArray(), $add_rules);
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes(): array
    {
        $add_attributes = [
            'employee_id' => '担当者',
            'order_status' => '状態',
            'order_status.*' => '状態',
            'supplier_id' => '仕入先',
            'department_id' => '部門',
            'office_facility_id' => '事業所',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray('仕入'), $add_attributes);
    }

    /**
     * デフォルトセット
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'order_date' => [
                'start' => Carbon::now()->startOfMonth()->toDateString(),
                'end' => Carbon::now()->endOfMonth()->toDateString(),
            ],
        ];
    }
}
