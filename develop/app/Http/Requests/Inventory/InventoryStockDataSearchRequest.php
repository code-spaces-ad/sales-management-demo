<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Inventory;

use App\Enums\SortTypes;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 在庫入力編集用 リクエストクラス
 */
class InventoryStockDataSearchRequest extends FormRequest
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
        // コード値(To)のチェック内容
        $code_end_validation_rule = ['bail', 'nullable', 'numeric'];
        if (!empty($this->code['start'])) {
            $code_end_validation_rule[] = 'gte:code.start';
        }

        return [
            'id' => ['bail', 'nullable', 'array'],
            'id.start' => ['bail', 'nullable', 'integer'],
            'id.end' => ['bail', 'nullable', 'integer'],
            'code' => ['bail', 'nullable', 'array'],
            'code.start' => ['bail', 'nullable', 'numeric'],
            'code.end' => $code_end_validation_rule,
            'warehouse_name' => ['bail', 'nullable', 'string'],
            'name_kana' => ['bail', 'nullable', 'string'],
            'product_id' => ['bail', 'nullable', 'integer'],
            'category_id' => ['bail', 'nullable', 'integer'],
            'warehouse_id' => ['bail', 'nullable', 'integer'],
            'adjust_stocks' => ['bail', 'nullable', 'integer'],
            'sort' => ['bail', 'nullable', 'integer'],
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
            'id.start' => 'ID（開始）',
            'id.end' => 'ID（終了）',
            'code' => 'コード',
            'code.start' => 'コード（開始）',
            'code.end' => 'コード（終了）',
            'warehouse_name' => '倉庫名',
            'name_kana' => '倉庫名かな',
            'product_id' => '商品ID',
            'category_id' => 'カテゴリーID',
            'warehouse_id' => '倉庫ID',
            'adjust_stocks' => '在庫調整フラグ',
            'sort' => 'ソート',
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
            'product_id' => null,
            'category_id' => null,
            'warehouse_id' => null,
            'sort' => SortTypes::SYLLABARY_ORDER,
        ];
    }
}
