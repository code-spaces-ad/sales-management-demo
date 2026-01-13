<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class CategorySearchRequest extends FormRequest
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
        return [
            'code' => 'bail|nullable|array',
            'code.start' => 'bail|nullable|string',
            'code.end' => 'bail|nullable|string',
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
            'code' => 'コード',
            'code.start' => 'コード（開始）',
            'code.end' => 'コード（終了）',
            'name' => 'カテゴリー名',
        ];
    }
}
