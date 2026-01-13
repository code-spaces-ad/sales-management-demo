<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterClassifications1Const;
use App\Models\Master\MasterClassification1;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 分類1マスター作成編集用 リクエストクラス
 */
class Classification1EditRequest extends FormRequest
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
        $table = with(new MasterClassification1())->getTable();    // テーブル名
        $classification1_id = $this->route()->parameter('classification1')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($classification1_id, 'code')->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterClassifications1Const::CODE_MIN_VALUE,
                'max:' . MasterClassifications1Const::CODE_MAX_VALUE, $unique_code],
            'name' => ['bail', 'required', 'string', 'max:' . MasterClassifications1Const::NAME_MAX_LENGTH],
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
            'name' => '分類1名',
        ];
    }
}
