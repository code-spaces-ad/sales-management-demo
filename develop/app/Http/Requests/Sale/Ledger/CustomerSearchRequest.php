<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Sale\Ledger;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterCustomer;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 得意先元帳検索用 リクエストクラス
 */
class CustomerSearchRequest extends FormRequest
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

        $add_rules = [
            'customer_id' => ['bail', 'nullable', 'integer', $exists_customer],
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
            'customer_id' => '得意先ID',
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
            'customer_id' => MasterCustomer::query()->orderBy('sort_code')->value('id'),
        ];
    }
}
