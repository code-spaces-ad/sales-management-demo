<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 事業所コードマスター検索用 リクエストクラス
 */
class OfficeFacilitySearchRequest extends FormRequest
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
            'department_id' => 'bail|nullable|integer',
            'department_id_code' => 'bail|nullable|integer',
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
            'department_id' => '部門ID',
            'department_id_code' => '部門コード',
            'name' => '事業所名',
        ];
    }
}
