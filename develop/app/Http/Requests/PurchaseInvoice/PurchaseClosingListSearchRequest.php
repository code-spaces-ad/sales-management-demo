<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\PurchaseInvoice;

use App\Models\Master\MasterSupplier;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 仕入締一覧検索用 リクエストクラス
 */
class PurchaseClosingListSearchRequest extends FormRequest
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
            'purchase_date' => ['bail', 'nullable'],
            'closing_date' => ['bail', 'nullable'],
            'supplier_id' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterSupplier())->getTable() . ',id'],
            'department_id' => ['bail', 'nullable', 'int'],
            'office_facility_id' => ['bail', 'nullable', 'int'],

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
            'purchase_date' => '仕入日',
            'closing_date' => '締年月日',
            'supplier_id' => '仕入先',
            'department_id' => '部門',
            'office_facility_id' => '事業所',
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
            'supplier_id' => null,
            'purchase_date' => Carbon::now()->format('Y-m'),
            'closing_date' => 0,
            'department_id' => 1,
            'office_facility_id' => 1,
        ];
    }
}
