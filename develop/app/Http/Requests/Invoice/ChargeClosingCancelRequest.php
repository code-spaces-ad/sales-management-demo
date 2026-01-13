<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 請求締処理解除用 リクエストクラス
 */
class ChargeClosingCancelRequest extends FormRequest
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
            'charge_data_ids' => ['bail', 'required', 'string'],
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
            'charge_data_ids' => '請求データIDS',
        ];
    }
}
