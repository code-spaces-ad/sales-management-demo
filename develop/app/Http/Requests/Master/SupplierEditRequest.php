<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterSuppliersConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Master\MasterSupplier;
use App\Rules\KanaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 仕入先マスター作成編集用 リクエストクラス
 */
class SupplierEditRequest extends FormRequest
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
        $supplier_id = $this->route()->parameter('supplier')->id ?? null;

        // Validation定義
        $min_value_code = 'min:' . MasterSuppliersConst::CODE_MIN_VALUE;
        $max_value_code = 'max:' . MasterSuppliersConst::CODE_MAX_VALUE;
        $max_len_name = 'max:' . MasterSuppliersConst::NAME_MAX_LENGTH;
        $max_len_name_kana = 'max:' . MasterSuppliersConst::NAME_KANA_MAX_LENGTH;
        $size_postal_code1 = 'size:' . MasterSuppliersConst::POSTAL_CODE1_MAX_LENGTH;
        $size_postal_code2 = 'size:' . MasterSuppliersConst::POSTAL_CODE2_MAX_LENGTH;
        $max_len_address1 = 'max:' . MasterSuppliersConst::ADDRESS1_MAX_LENGTH;
        $max_len_address2 = 'max:' . MasterSuppliersConst::ADDRESS2_MAX_LENGTH;
        $max_len_tel_number = 'max:' . MasterSuppliersConst::TEL_NUMBER_MAX_LENGTH;
        $max_len_fax_number = 'max:' . MasterSuppliersConst::FAX_NUMBER_MAX_LENGTH;
        $max_len_email = 'max:' . MasterSuppliersConst::EMAIL_MAX_LENGTH;
        $min_value_closing_date = 'min:' . MasterSuppliersConst::CLOSING_DATE_MIN_VALUE;
        $max_value_closing_date = 'max:' . MasterSuppliersConst::CLOSING_DATE_MAX_VALUE;
        $max_len_note = 'max:' . MasterSuppliersConst::NOTE_MAX_LENGTH;

        return [
            'code' => [
                'bail', 'required', 'numeric', $min_value_code, $max_value_code,
                Rule::unique(with(new MasterSupplier())->getTable())
                    ->ignore($supplier_id)->whereNull('deleted_at'),
            ],
            'name' => ['bail', 'required', 'string', $max_len_name],
            //            'name_kana' => ['bail', 'nullable', 'string', $max_len_name_kana, new KanaRule()],
            'name_kana' => ['bail', 'nullable', 'string', $max_len_name_kana],
            'postal_code1' => ['bail', 'nullable', 'string', $size_postal_code1],
            'postal_code2' => ['bail', 'nullable', 'string', $size_postal_code2],
            'address1' => ['bail', 'required', 'string', $max_len_address1],
            'address2' => ['bail', 'nullable', 'string', $max_len_address2],
            'tel_number' => ['bail', 'nullable', 'string', $max_len_tel_number],
            'fax_number' => ['bail', 'nullable', 'string', $max_len_fax_number],
            'email' => ['bail', 'nullable', 'string', 'email', $max_len_email],
            'tax_calc_type_id' => ['bail', 'nullable', 'integer'],
            'tax_rounding_method_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterRoundingMethod())->getTable() . ',id,deleted_at,NULL', ],
            'transaction_type_id' => ['bail', 'nullable', 'integer'],
            'closing_date' => ['bail', 'nullable', 'integer', $min_value_closing_date, $max_value_closing_date],
            'billing_supplier_id' => [
                'bail', 'nullable', 'integer',
                'exists:' . with(new MasterSupplier())->getTable() . ',id',
            ],
            'start_account_receivable_balance' => ['bail', 'nullable', 'integer'],
            'note' => ['bail', 'nullable', 'string', $max_len_note],
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
            'code' => '仕入先コード',
            'name' => '仕入先名',
            'name_kana' => '仕入先名カナ',
            'postal_code1' => '郵便番号１',
            'postal_code2' => '郵便番号２',
            'address1' => '住所１',
            'address2' => '住所２',
            'tel_number' => '電話番号',
            'fax_number' => 'FAX番号',
            'email' => 'メールアドレス',
            'tax_calc_type_id' => '税計算区分',
            'tax_rounding_method_id' => '税額端数処理',
            'transaction_type_id' => '取引種別',
            'closing_date' => '支払締日',
            'billing_supplier_id' => '支払先',
            'start_account_receivable_balance' => '開始買掛残高',
            'note' => '備考',
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
