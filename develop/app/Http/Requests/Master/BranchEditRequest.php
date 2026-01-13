<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterBranchesConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Rules\KanaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 支所マスター作成編集用 リクエストクラス
 */
class BranchEditRequest extends FormRequest
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
        $max_len_name_kana = 'max:' . MasterBranchesConst::NAME_KANA_MAX_LENGTH;

        $name_validation_rule = ['bail', 'required', 'string'];
        //        $name_kana_validation_rule = ['bail', 'nullable', 'string', $max_len_name_kana, new KanaRule()];
        $name_kana_validation_rule = ['bail', 'nullable', 'string', $max_len_name_kana];
        $mnemonic_name_validation_rule = ['bail', 'required', 'string',
            'max:' . MasterBranchesConst::MNEMONIC_NAME_MAX_LENGTH, ];
        $customer_id_validation_rule = ['bail', 'required', 'integer'];

        return [
            'branch_name' => $name_validation_rule,
            'name_kana' => $name_kana_validation_rule,
            'mnemonic_name' => $mnemonic_name_validation_rule,
            'customer_id' => $customer_id_validation_rule,
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
            'branch_name' => '支所名',
            'name_kana' => '支所名かな',
            'mnemonic_name' => '支所名略称',
            'customer_id' => '得意先ID',
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
