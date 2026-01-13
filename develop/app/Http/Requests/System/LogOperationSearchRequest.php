<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\System;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 操作ログ検索用 リクエストクラス
 */
class LogOperationSearchRequest extends FormRequest
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
    public function rules()
    {
        return [
            'created_at' => 'bail|nullable|array',
            'created_at.start' => 'bail|nullable|date',
            'created_at.end' => 'bail|nullable|date|after_or_equal:created_at.start',
        ];
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'created_at' => '操作日時',
            'created_at.start' => '操作日時（開始）',
            'created_at.end' => '操作日時（終了）',
        ];
    }

    /**
     * デフォルトセット
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'created_at' => [
                'start' => Carbon::now()->startOfMonth()->toDateString(),
                'end' => Carbon::now()->endOfMonth()->toDateString(),
            ],
        ];
    }
}
