<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Receive;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterRecipient;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 受注伝票一覧検索用 リクエストクラス
 */
class OrdersReceivedSearchRequest extends FormRequest
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
            'employee_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterEmployee())->getTable() . ',id', ],
            'customer_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterCustomer())->getTable() . ',id', ],
            'branch_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterBranch())->getTable() . ',id', ],
            'recipient_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterRecipient())->getTable() . ',id', ],
            'undelivered_only' => ['bail', 'nullable', 'integer'],
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
            'customer_id' => '請求先',
            'branch_id' => '支所',
            'recipient_id' => '納品先',
            'undelivered_only' => '未納品',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray('受注'), $add_attributes);
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
