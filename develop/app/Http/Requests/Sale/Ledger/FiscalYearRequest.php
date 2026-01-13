<?php

namespace App\Http\Requests\Sale\Ledger;

use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class FiscalYearRequest extends FormRequest
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
            'fiscal_year' => ['bail', 'nullable'],
            'order_date' => ['bail', 'nullable', 'array'],
            'order_date.start' => ['bail', 'nullable', 'date'],
            'order_date.end' => ['bail', 'nullable', 'date', 'after_or_equal:charge_date.start'],
            'aggregation_type' => ['bail', 'nullable', 'string'],
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
            'fiscal_year' => '会計年度',
            'aggregation_type' => '集計種別',
        ];
    }

    /**
     * デフォルトセット
     *
     * @return array
     */
    public function defaults(): array
    {
        $fiscal_year = Carbon::now()->format('Y');
        [$start_date, $end_date] = DateHelper::getFiscalYearRange($fiscal_year);

        return [
            'order_date' => [
                'start' => $start_date,
                'end' => $end_date,
            ],
            'fiscal_year' => $fiscal_year,
            'aggregation_type' => 'sub_total',
        ];
    }
}
