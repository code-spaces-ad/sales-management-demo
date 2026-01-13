<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput\Sale;

use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterProduct;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SummarySalesByCustomerProductDaySearchRequest extends FormRequest
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
            'customer_id' => ['bail', 'nullable', 'array'],
            'customer_id.start' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterCustomer())->getTable() . ',id'],
            'customer_id.end' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterCustomer())->getTable() . ',id', 'gte:customer_id.start'],
            'product_id' => ['bail', 'nullable', 'array'],
            'product_id.start' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterProduct())->getTable() . ',id'],
            'product_id.end' => ['bail', 'nullable', 'int',
                'exists:' . with(new MasterProduct())->getTable() . ',id', 'gte:product_id.start'],
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
            'customer_id' => '得意先',
            'customer_id.start' => '得意先（開始日）',
            'customer_id.end' => '得意先（終了日）',
            'product_id' => '商品',
            'product_id.start' => '商品（開始日）',
            'product_id.end' => '商品（終了日）',
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
