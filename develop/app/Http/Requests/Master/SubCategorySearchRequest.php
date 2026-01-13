<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

/**
 * サブカテゴリマスター検索用 リクエストクラス
 */
class SubCategorySearchRequest extends FormRequest
{
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
        // コード値(To)のチェック内容
        $category_code_end_validation_rule = ['bail', 'nullable', 'numeric'];
        if (!empty($this->code['start'])) {
            $category_code_end_validation_rule[] = 'gte:category_code.start';
        }
        $sub_category_code_end_validation_rule = ['bail', 'nullable', 'numeric'];
        if (!empty($this->code['start'])) {
            $sub_category_code_end_validation_rule[] = 'gte:sub_category_code.start';
        }

        return [
            'id' => 'bail|nullable|array',
            'id.start' => 'bail|nullable|integer',
            'id.end' => 'bail|nullable|integer',
            'category_code' => 'bail|nullable|array',
            'category_code.start' => 'bail|nullable|numeric',
            'category_code.end' => $category_code_end_validation_rule,
            'sub_category_code' => 'bail|nullable|array',
            'sub_category_code.start' => 'bail|nullable|numeric',
            'sub_category_code.end' => $sub_category_code_end_validation_rule,
            'category_id' => 'bail|nullable|integer',
            'name' => 'bail|nullable|string',
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
            'category_id' => 'カテゴリー',
            'name' => 'サブカテゴリ名',
        ];
    }
}
