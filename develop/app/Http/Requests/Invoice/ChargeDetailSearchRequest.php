<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Invoice;

use App\Models\Master\MasterCustomer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 請求明細一覧検索用 リクエストクラス
 */
class ChargeDetailSearchRequest extends FormRequest
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
            'customer_id' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterCustomer())->getTable() . ',id'],
            'charge_date' => ['bail', 'nullable'],
            'closing_date' => ['bail', 'nullable'],
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
            'customer_id' => '得意先ID',
            'charge_date' => '請求日',
            'closing_date' => '仕入締日',
        ];
    }
}
