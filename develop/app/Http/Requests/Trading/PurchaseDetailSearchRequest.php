<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Trading;

use App\Models\Master\MasterSupplier;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 発注明細一覧検索用 リクエストクラス
 */
class PurchaseDetailSearchRequest extends FormRequest
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
            'supplier_id' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterSupplier())->getTable() . ',id'],
            'billing_date' => ['bail', 'nullable'],
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
            'supplier_id' => '得意先ID',
            'billing_date' => '請求日',
        ];
    }
}
