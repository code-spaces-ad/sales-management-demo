<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 商品マスター検索用 リクエストクラス
 */
class ProductSearchRequest extends FormRequest
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
        // コード値(To)のチェック内容
        $code_end_validation_rule = ['bail', 'nullable', 'numeric'];
        if (!empty($this->code['start'])) {
            $code_end_validation_rule[] = 'gte:code.start';
        }

        return [
            'id' => 'bail|nullable|array',
            'id.start' => 'bail|nullable|integer',
            'id.end' => 'bail|nullable|integer',
            'code' => 'bail|nullable|array',
            'code.start' => 'bail|nullable|numeric',
            'code.end' => $code_end_validation_rule,
            'name' => 'bail|nullable|string',
            'category_id' => 'bail|nullable|integer',
            'sub_category_id' => 'bail|nullable|integer',
            'customer_product_code' => 'bail|nullable',
            'kind_id' => 'bail|nullable|integer',
            'classification1_id' => 'bail|nullable|integer',
            'classification2_id' => 'bail|nullable|integer',
            'reduced_tax_flag' => 'bail|nullable|array',
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
            'id' => 'ID',
            'id.start' => 'ID（開始）',
            'id.end' => 'ID（終了）',
            'code' => 'コード',
            'code.start' => 'コード（開始）',
            'code.end' => 'コード（終了）',
            'name' => '商品名',
            'category_id' => 'カテゴリー',
            'sub_category_id' => 'サブカテゴリー',
            'customer_product_code' => '相手先商品番号',
            'kind_id' => '種別',
            'classification1_id' => '分類１',
            'classification2_id' => '分類２',
            'reduced_tax_flag' => '税率区分',
        ];
    }
}
