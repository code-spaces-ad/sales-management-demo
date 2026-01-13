<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\PurchaseInvoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 仕入締処理解除用 リクエストクラス
 */
class PurchaseClosingCancelRequest extends FormRequest
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
            'purchase_data_ids' => ['bail', 'required', 'string'],
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
            'purchase_data_ids' => '請求データIDS',
        ];
    }
}
