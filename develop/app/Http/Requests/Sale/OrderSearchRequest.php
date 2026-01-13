<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Sale;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterRecipient;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 売上伝票一覧検索用 リクエストクラス
 */
class OrderSearchRequest extends FormRequest
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
            'orders_received_number' => ['bail', 'nullable'],
            'billing_date' => ['bail', 'nullable', 'array'],
            'billing_date.start' => ['bail', 'nullable', 'date'],
            'billing_date.end' => ['bail', 'nullable', 'date', 'after_or_equal:billing_date.start'],
            'billing_month' => ['bail', 'nullable', 'date'],
            'customer_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterCustomer())->getTable() . ',id', ],
            'branch_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterBranch())->getTable() . ',id', ],
            'recipient_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterRecipient())->getTable() . ',id', ],
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
            'orders_received_number' => '受注番号',
            'billing_date' => '請求日付',
            'billing_date.start' => '請求日付（開始日）',
            'billing_date.end' => '請求日付（終了日）',
            'billing_month' => '請求月',
            'customer_id' => '得意先',
            'branch_id' => '支所',
            'recipient_id' => '納品先',
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
        ];
    }
}
