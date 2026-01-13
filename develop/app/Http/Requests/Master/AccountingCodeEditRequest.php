<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterAccountingCodesConst;
use App\Models\Master\MasterAccountingCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 経理コードマスター作成編集用 リクエストクラス
 */
class AccountingCodeEditRequest extends FormRequest
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
        $table = with(new MasterAccountingCode())->getTable();    // テーブル名
        $accounting_code_id = $this->route()->parameter('accounting_code')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($accounting_code_id, 'code')->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterAccountingCodesConst::CODE_MIN_VALUE,
                'max:' . MasterAccountingCodesConst::CODE_MAX_VALUE, $unique_code],
            'name' => ['bail', 'required', 'string', 'max:' . MasterAccountingCodesConst::NAME_MAX_LENGTH],
            'note' => ['bail', 'nullable', 'string', 'max:' . MasterAccountingCodesConst::NOTE_MAX_LENGTH],
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
            'name' => '経理コード名',
            'note' => '備考',
        ];
    }
}
