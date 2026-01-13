<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterRecipientConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Rules\KanaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 納品先マスター作成編集用 リクエストクラス
 */
class RecipientEditRequest extends FormRequest
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
        $max_len_name_kana = 'max:' . MasterRecipientConst::NAME_KANA_MAX_LENGTH;

        $name_validation_rule = ['bail', 'required', 'string'];
        //        $name_kana_validation_rule = ['bail', 'nullable', 'string', $max_len_name_kana, new KanaRule()];
        $name_kana_validation_rule = ['bail', 'nullable', 'string', $max_len_name_kana];
        $branch_id_validation_rule = ['bail', 'required', 'integer'];

        return [
            'recipient_name' => $name_validation_rule,
            'name_kana' => $name_kana_validation_rule,
            'branch_id' => $branch_id_validation_rule,
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
            'recipient_name' => '納品先名',
            'name_kana' => '納品先名',
            'branch_id' => '支所ID',
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
