<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Sale\Ledger;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterProduct;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 商品台帳検索用 リクエストクラス
 */
class ProductsSearchRequest extends FormRequest
{
    use SearchRequestTrait;

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
        $add_rules = [
            'product_id' => ['bail', 'nullable', 'integer'],
        ];

        // 共通化しているルール + $add_rulesに指定したルールをreturn
        return array_merge_recursive($this->setRulesArray(), $add_rules);
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes(): array
    {
        $add_attributes = [
            'product_id' => '商品ID',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray(), $add_attributes);
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
            'product_id' => MasterProduct::query()->oldest('name_kana')->value('id'),
        ];
    }
}
