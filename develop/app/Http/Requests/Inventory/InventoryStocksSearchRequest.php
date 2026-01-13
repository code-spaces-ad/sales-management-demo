<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Inventory;

use App\Models\Master\MasterProduct;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 在庫検索用 リクエストクラス
 */
class InventoryStocksSearchRequest extends FormRequest
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
            'product_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterProduct())->getTable() . ',id', ],
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
            'product_id' => '商品ID',
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
            'product_id' => MasterProduct::query()->oldest('name_kana')->value('id'),
        ];
    }
}
