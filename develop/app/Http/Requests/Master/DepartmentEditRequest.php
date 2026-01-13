<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterDepartmentsConst;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Rules\KanaRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 部門マスター作成編集用 リクエストクラス
 */
class DepartmentEditRequest extends FormRequest
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
        $table = with(new MasterDepartment())->getTable();    // テーブル名
        $department_id = $this->route()->parameter('department')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($department_id, 'code')->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterDepartmentsConst::CODE_MIN_VALUE,
                'max:' . MasterDepartmentsConst::CODE_MAX_VALUE, $unique_code],
            'name' => ['bail', 'required', 'string', 'max:' . MasterDepartmentsConst::NAME_MAX_LENGTH],
            //            'name_kana' => ['bail', 'nullable', 'string',
            //                'max:' . MasterDepartmentsConst::NAME_KANA_MAX_LENGTH, new KanaRule()],
            'name_kana' => ['bail', 'nullable', 'string', 'max:' . MasterDepartmentsConst::NAME_KANA_MAX_LENGTH],
            'manager_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterEmployee())->getTable() . ',id'],
            'mnemonic_name' => ['bail', 'nullable', 'string', 'max:' . MasterDepartmentsConst::MNEMONIC_NAME_MAX_LENGTH],
            'note' => ['bail', 'nullable', 'string', 'max:' . MasterDepartmentsConst::NOTE_MAX_LENGTH],
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
            'name' => '部門名',
            'name_kana' => '部門カナ',
            'manager_id' => '責任者',
            'mnemonic_name' => '略称',
            'note' => '備考',
        ];
    }
}
