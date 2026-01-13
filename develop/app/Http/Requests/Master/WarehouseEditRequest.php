<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterWarehousesConst;
use App\Models\Master\MasterWarehouse;
use App\Rules\KanaRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 倉庫マスター作成編集用 リクエストクラス
 */
class WarehouseEditRequest extends FormRequest
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
        // コード値の確認
        $table = with(new MasterWarehouse())->getTable();    // テーブル名
        $warehouse_id = $this->route()->parameter('warehouse')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($warehouse_id, 'code')->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterWarehousesConst::CODE_MIN_VALUE,
                'max:' . MasterWarehousesConst::CODE_MAX_VALUE, $unique_code],
            'name' => ['bail', 'required', 'string', 'max:' . MasterWarehousesConst::NAME_MAX_LENGTH],
            //            'name_kana' => ['bail', 'nullable', 'string', 'max:' . MasterWarehousesConst::NAME_KANA_MAX_LENGTH, new KanaRule()],
            'name_kana' => ['bail', 'nullable', 'string', 'max:' . MasterWarehousesConst::NAME_KANA_MAX_LENGTH],
            'is_control_inventory' => ['bail', 'required', 'integer'],
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
            'code' => 'コード',
            'name' => '倉庫名',
            'name_kana' => '倉庫名かな',
            'is_control_inventory' => '在庫管理',
        ];
    }
}
