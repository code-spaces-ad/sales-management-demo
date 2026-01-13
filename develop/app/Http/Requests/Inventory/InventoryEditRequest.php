<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Inventory;

use App\Consts\DB\Inventory\InventoryDataDetailConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Models\Master\MasterEmployee;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 在庫入力編集用 リクエストクラス
 */
class InventoryEditRequest extends FormRequest
{
    use EditRequestTrait;

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
            'inout_date' => ['bail', 'required', 'date'],
            'from_warehouse_id' => ['bail', 'required', 'integer'],
            'to_warehouse_id' => ['bail', 'required', 'integer'],
            'employee_id' => ['bail', 'required', 'integer', 'exists:' . with(new MasterEmployee())->getTable() . ',id'],
            'detail' => ['bail', 'required', 'array', 'min:1'],
            'detail.*.product_id' => ['bail', 'required', 'numeric'],
            'detail.*.product_name' => ['bail', 'required', 'string',
                'max:' . InventoryDataDetailConst::PRODUCT_NAME_MAX_LENGTH, ],
            'detail.*.quantity' => ['bail', 'required', 'numeric'],
            'detail.*.note' => ['bail', 'nullable', 'string',
                'max:' . InventoryDataDetailConst::NOTE_MAX_LENGTH, ],
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
            'inout_date' => '入出庫日',
            'from_warehouse_id' => '移動元倉庫',
            'to_warehouse_id' => '移動先倉庫',
            'employee_id' => '担当者',
            'detail' => '商品',
            'detail.*.product_id' => '商品ID',
            'detail.*.product_name' => '商品名',
            'detail.*.quantity' => '数量',
            'detail.*.note' => '備考',
        ];
    }

    /**
     * バリデーションエラー時の処理
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        $this->setTokenAndRedirect($this, $validator);
    }
}
