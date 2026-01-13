<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Inventory;

use App\Enums\StorehouseStatus;
use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterProduct;
use App\Rules\EnumValueCustom;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 在庫一覧検索用 リクエストクラス
 */
class ProductsMovingSearchRequest extends FormRequest
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
            'Storehouse_Status' => ['bail', 'nullable', 'array'],
            'Storehouse_Status.*' => ['bail', 'nullable', 'integer', new EnumValueCustom(StorehouseStatus::class, false)],
            'product_id' => ['bail', 'nullable', 'integer'],
            'warehouse_id' => ['bail', 'nullable', 'integer'],
            'closing_ym' => ['bail', 'nullable', 'integer'],
        ];

        return array_merge_recursive($this->setRulesArray('inout_date'), $add_rules);
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes(): array
    {
        $add_attributes = [
            'Storehouse_Status' => '状態',
            'Storehouse_Status.*' => '状態',
            'product_id' => '商品ID',
            'warehouse_id' => '倉庫ID',
            'closing_ym' => '締年月',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray('入出庫日', 'inout_date'), $add_attributes);
    }

    /**
     * デフォルトセット
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'inout_date' => [
                'start' => Carbon::now()->startOfMonth()->toDateString(),
                'end' => Carbon::now()->endOfMonth()->toDateString(),
            ],
            'product_id' => MasterProduct::query()->oldest('name_kana')->value('id'),
            'warehouse_id' => null,
        ];
    }
}
