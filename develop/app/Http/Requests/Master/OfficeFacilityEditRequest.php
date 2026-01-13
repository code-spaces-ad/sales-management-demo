<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterOfficeFacilitiesConst;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterOfficeFacility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 事業所コードマスター作成編集用 リクエストクラス
 */
class OfficeFacilityEditRequest extends FormRequest
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
        $table = with(new MasterOfficeFacility())->getTable();    // テーブル名
        $department_id = $this->department_id;
        $office_facility_id = $this->route()->parameter('office_facility')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($office_facility_id, 'code')
            ->where('department_id', $department_id)
            ->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterOfficeFacilitiesConst::CODE_MIN_VALUE,
                'max:' . MasterOfficeFacilitiesConst::CODE_MAX_VALUE, $unique_code],
            'name' => ['bail', 'required', 'string', 'max:' . MasterOfficeFacilitiesConst::NAME_MAX_LENGTH],
            'department_id' => ['bail', 'required', 'integer', 'exists:' . with(new MasterDepartment())->getTable() . ',id'],
            'manager_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterEmployee())->getTable() . ',id'],
            'note' => ['bail', 'nullable', 'string', 'max:' . MasterOfficeFacilitiesConst::NOTE_MAX_LENGTH],
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
            'department_id' => '部門',
            'code' => 'コード',
            'name' => '事業所名',
            'note' => '備考',
        ];
    }
}
