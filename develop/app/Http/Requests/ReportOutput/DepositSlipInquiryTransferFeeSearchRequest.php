<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DepositSlipInquiryTransferFeeSearchRequest extends FormRequest
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
            'payment_date' => ['bail', 'nullable', 'array'],
            'payment_date.start' => ['bail', 'nullable', 'date'],
            'payment_date.end' => ['bail', 'nullable', 'date', 'after_or_equal:payment_date.start'],
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
            'payment_date' => '支払日',
            'payment_date.start' => '支払日（開始日）',
            'payment_date.end' => '支払日（終了日）',
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
            'payment_date' => [
                'start' => Carbon::now()->startOfMonth()->toDateString(),
                'end' => Carbon::now()->endOfMonth()->toDateString(),
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
