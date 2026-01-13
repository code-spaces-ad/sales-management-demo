<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput\Trading;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseDetailsListSearchRequest extends FormRequest
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
            'purchase_date' => ['bail', 'nullable', 'array'],
            'purchase_date.start' => ['bail', 'nullable', 'date'],
            'purchase_date.end' => ['bail', 'nullable', 'date', 'after_or_equal:sales_date.start'],
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
            'purchase_date' => '入荷日',
            'purchase_date.start' => '入荷日（開始日）',
            'purchase_date.end' => '入荷日（終了日）',
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
            'purchase_date' => [
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
