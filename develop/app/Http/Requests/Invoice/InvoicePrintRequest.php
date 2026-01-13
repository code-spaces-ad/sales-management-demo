<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 請求書発行用 リクエストクラス
 */
class InvoicePrintRequest extends FormRequest
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
            'customer_ids' => ['bail', 'required', 'string'],
            'customer_names' => ['bail', 'required', 'string'],
            'issue_date' => ['bail', 'required', 'string'],
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
            'charge_data_ids' => '請求IDS',
            'customer_ids' => '請求先IDS',
            'customer_names' => '請求先名',
            'issue_date' => '発行年月日',
        ];
    }
}
