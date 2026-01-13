<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Inventory;

use App\Consts\DB\Master\MasterWarehousesConst;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 在庫入力編集用 リクエストクラス
 */
class InventoryStockDataEditRequest extends FormRequest
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
        $max_len_name = 'max:' . MasterWarehousesConst::NAME_MAX_LENGTH;

        return [
            'id' => ['bail', 'required', 'numeric'],
            'inventory_stocks' => ['bail', 'required', 'numeric'],
            'warehouse_name' => ['bail', 'required', 'string', $max_len_name],
            'product_name' => ['bail', 'required', 'string', $max_len_name],
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
            'id' => 'ID',
            'inventory_stocks' => '在庫数量',
            'warehouse_name' => '倉庫名',
            'product_name' => '商品名',
        ];
    }
}
