<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Sale;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterCustomer;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 入金伝票一覧検索用 リクエストクラス
 */
class DepositSearchRequest extends FormRequest
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
        $add_rules = [
            'customer_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterCustomer())->getTable() . ',id', ],
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
            'customer_id' => '得意先',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray('入金'), $add_attributes);
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
