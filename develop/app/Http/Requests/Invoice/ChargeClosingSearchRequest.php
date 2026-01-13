<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Invoice;

use App\Models\Master\MasterCustomer;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 請求締処理検索用 リクエストクラス
 */
class ChargeClosingSearchRequest extends FormRequest
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
            'charge_date' => ['bail', 'nullable'],
            'closing_date' => ['bail', 'nullable'],
            'customer_id' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterCustomer())->getTable() . ',id'],
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
            'customer_id' => '得意先',
            'charge_date' => '請求日',
            'closing_date' => '締年月日',
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
            'customer_id' => null,
            'charge_date' => Carbon::now()->format('Y-m'),
            'closing_date' => 0,
            'department_id' => 1,
            'office_facility_id' => 1,
        ];
    }
}
