<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ダッシュボードお知らせ作成編集用 リクエストクラス
 */
class DashboardEditRequest extends FormRequest
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

            'news' => ['bail'],

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
            'news' => '共有メモ',

        ];
    }
}
