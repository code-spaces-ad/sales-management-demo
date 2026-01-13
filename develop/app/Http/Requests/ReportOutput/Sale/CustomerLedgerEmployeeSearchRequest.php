<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput\Sale;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterEmployee;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CustomerLedgerEmployeeSearchRequest extends FormRequest
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
        $table_customer = with(new MasterCustomer())->getTable();    // テーブル名
        $exists_customer = "exists:{$table_customer},id";
        $table_employee = with(new MasterEmployee())->getTable();    // テーブル名
        $exists_employee = "exists:{$table_employee},id";

        $add_rules = [
            'customer_id.start' => ['bail', 'nullable', 'integer', $exists_customer],
            'customer_id.end' => ['bail', 'nullable', 'integer', $exists_customer],
            'employee_id' => ['bail', 'nullable', 'integer', $exists_employee],
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
            'customer_id.start' => '得意先（開始）',
            'customer_id.end' => '得意先（終了）',
            'employee_id' => '担当者',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray(), $add_attributes);
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
            'customer_id' => [
                'start' => MasterCustomer::query()->orderBy('sort_code')->value('id'),
                'end' => MasterCustomer::query()->orderBy('sort_code')->value('id'),
            ],
            'employee_id' => MasterEmployee::query()->orderBy('code')->value('id'),
        ];
    }
}
