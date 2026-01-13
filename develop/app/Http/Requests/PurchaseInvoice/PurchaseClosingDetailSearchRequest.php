<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\PurchaseInvoice;

use App\Models\Master\MasterSupplier;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 仕入締明細一覧検索用 リクエストクラス
 */
class PurchaseClosingDetailSearchRequest extends FormRequest
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
            'purchase_date' => ['bail', 'nullable'],
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
            'supplier_id' => '仕入先ID',
            'purchase_date' => '仕入日',
            'closing_date' => '仕入締区分',
        ];
    }
}
