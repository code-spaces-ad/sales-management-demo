<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 分類1マスター検索用 リクエストクラス
 */
class Classification1SearchRequest extends FormRequest
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
            'name' => '分類1名',
        ];
    }
}
