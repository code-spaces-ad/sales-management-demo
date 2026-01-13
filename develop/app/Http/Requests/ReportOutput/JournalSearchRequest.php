<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class JournalSearchRequest extends FormRequest
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
            'order_date' => ['bail', 'nullable', 'array'],
            'order_date.start' => ['bail', 'nullable', 'date'],
            'order_date.end' => ['bail', 'nullable', 'date', 'after_or_equal:order_date.start'],
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
            'order_date' => '伝票日付',
            'order_date.start' => '伝票日付（開始日）',
            'order_date.end' => '伝票日付（終了日）',
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
            'order_date' => [
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
