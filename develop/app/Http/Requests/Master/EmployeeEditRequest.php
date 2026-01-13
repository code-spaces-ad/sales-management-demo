<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterEmployeesConst;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterOfficeFacility;
use App\Rules\KanaRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 担当マスター作成編集用 リクエストクラス
 */
class EmployeeEditRequest extends FormRequest
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
        $table = with(new MasterEmployee())->getTable();    // テーブル名
        $employee_id = $this->route()->parameter('employee')->id ?? null;
        $unique_code = Rule::unique($table)->ignore($employee_id)->whereNull('deleted_at');
        //
        $min_value_code = 'min:' . MasterEmployeesConst::CODE_MIN_VALUE;
        $max_value_code = 'max:' . MasterEmployeesConst::CODE_MAX_VALUE;
        $max_len_name = 'max:' . MasterEmployeesConst::NAME_MAX_LENGTH;
        $max_len_name_kana = 'max:' . MasterEmployeesConst::NAME_KANA_MAX_LENGTH;
        $max_len_note = 'max:' . MasterEmployeesConst::NOTE_MAX_LENGTH;
        //
        $code_validation_rule = ['bail', 'required', 'numeric', $min_value_code, $max_value_code, $unique_code];
        $name_validation_rule = ['bail', 'required', 'string', $max_len_name];
        //        $name_kana_validation_rule = ['bail', 'nullable', 'string', $max_len_name_kana, new KanaRule()];
        $name_kana_validation_rule = ['bail', 'nullable', 'string', $max_len_name_kana];
        $birthday_validation_rules = ['bail', 'nullable', 'date'];
        $hire_date_validation_rules = ['bail', 'nullable', 'date'];
        $note_validation_rules = ['bail', 'nullable', 'string', $max_len_note];

        return [
            'code' => $code_validation_rule,
            'name' => $name_validation_rule,
            'name_kana' => $name_kana_validation_rule,
            'birthday' => $birthday_validation_rules,
            'hire_date' => $hire_date_validation_rules,
            'note' => $note_validation_rules,
            'department_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterDepartment())->getTable() . ',id',
                function ($attribute, $value, $fail) {
                    if ($value !== null) {
                        $officeFacilitiesId = request()->input('office_facilities_id');
                        if ($officeFacilitiesId === null) {
                            $fail(trans('validation.custom.department_check_office_id'));
                        }
                    }
                }],
            'office_facilities_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterOfficeFacility())->getTable() . ',id',
                function ($attribute, $value, $fail) {
                    if ($value !== null) {
                        $departmentId = request()->input('department_id');
                        if ($departmentId === null) {
                            $fail(trans('validation.custom.office_check_department_id'));
                        }

                        $officeFacility = MasterOfficeFacility::find($value);
                        if ($officeFacility && $officeFacility->department_id != $departmentId) {
                            $fail(trans('validation.custom.not_match_department_office'));
                        }
                    }
                }],
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
            'code' => '社員コード',
            'name' => '社員名',
            'name_kana' => '社員名かな',
            'birthday' => '生年月日',
            'hire_date' => '入社日',
            'note' => '備考',
            'department_id' => '部門',
            'office_facilities_id' => '事業所',
        ];
    }
}
