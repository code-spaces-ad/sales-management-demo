<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 顧客別単価マスター検索用 リクエストクラス
 */
class CustomerPriceSearchRequest extends FormRequest
{
    /**
     * Determine if the product is authorized to make this request.
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

        $id_validation_rule = ['bail', 'nullable', 'integer'];
        $name_validation_rule = ['bail', 'nullable', 'string'];

        // コード値(To)のチェック内容
        $code_end_validation_rule = ['bail', 'nullable', 'numeric'];
        if (!empty($this->code['start'])) {
            $code_end_validation_rule[] = 'gte:code.start';
        }

        return [
            'code.start' => 'bail|nullable|numeric',
            'code.end' => $code_end_validation_rule,
            'customer_id' => $id_validation_rule,
            'customer_id_code' => $id_validation_rule,
            'name' => $name_validation_rule,
        ];
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'code.start' => 'コード（開始）',
            'code.end' => 'コード（終了）',
            'customer_id' => '得意先ID',
            'customer_id_code' => '得意先コード',
            'name' => '商品名',
        ];
    }
}
