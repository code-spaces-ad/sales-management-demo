<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterKindsConst;
use App\Models\Master\MasterKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 種別マスター作成編集用 リクエストクラス
 */
class KindEditRequest extends FormRequest
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
        $table = with(new MasterKind())->getTable();    // テーブル名
        $kind_id = $this->route()->parameter('kind')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($kind_id, 'code')->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterKindsConst::CODE_MIN_VALUE,
                'max:' . MasterKindsConst::CODE_MAX_VALUE, $unique_code],
            'name' => ['bail', 'required', 'string', 'max:' . MasterKindsConst::NAME_MAX_LENGTH],
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
            'name' => '種別名',
        ];
    }
}
