<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput\Sale;

use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AccountsReceivableBalanceListByTaxRateSearchRequest extends FormRequest
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
        // 初期表示はバリデーションチェックしない
        if ($this->isInitialDisplay()) {
            return [];
        }

        return [
            'year_month' => ['bail', 'required', 'date_format:Y-m'],
            'department_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterDepartment())->getTable() . ',id', ],
            'office_facility_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterOfficeFacility())->getTable() . ',id', ],
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
            'year_month' => '年月度',
            'department_id' => '部門',
            'office_facilities_id' => '事業所',
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
            'year_month' => Carbon::now()->format('Y-m'),
        ];
    }

    /**
     * 初期表示かどうか判定
     *
     * @return bool
     */
    private function isInitialDisplay(): bool
    {
        return $this->isMethod('GET') && empty($this->query());
    }
}
