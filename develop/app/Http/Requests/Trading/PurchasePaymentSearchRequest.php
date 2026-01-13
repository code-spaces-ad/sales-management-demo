<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Trading;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterSupplier;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 支払伝票一覧検索用 リクエストクラス
 */
class PurchasePaymentSearchRequest extends FormRequest
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
            'transaction_type' => ['bail', 'nullable', 'array'],
            'transaction_type.*' => ['bail', 'nullable', 'integer'],
            'supplier_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterSupplier())->getTable() . ',id', ],
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
            'supplier_id' => '仕入先',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray('支払'), $add_attributes);
    }
}
