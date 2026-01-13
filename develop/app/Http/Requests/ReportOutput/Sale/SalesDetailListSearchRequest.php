<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput\Sale;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SalesDetailListSearchRequest extends FormRequest
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
            'sales_date' => ['bail', 'nullable', 'array'],
            'sales_date.start' => ['bail', 'nullable', 'date'],
            'sales_date.end' => ['bail', 'nullable', 'date', 'after_or_equal:sales_date.start'],
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
            'sales_date' => '売上日',
            'sales_date.start' => '売上日（開始日）',
            'sales_date.end' => '売上日（終了日）',
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
            'sales_date' => [
                'start' => Carbon::now()->format('Y-m-d'),
                'end' => Carbon::now()->format('Y-m-d'),
            ],
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
