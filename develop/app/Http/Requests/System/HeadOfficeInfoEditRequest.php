<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\System;

use App\Consts\DB\System\HeadOfficeInfoConst;
use App\Http\Requests\Define\EditRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 会社情報作成編集用 リクエストクラス
 */
class HeadOfficeInfoEditRequest extends FormRequest
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
        // Validation定義
        $max_len_company_name = 'max:' . HeadOfficeInfoConst::COMPANY_NAME_MAX_LENGTH;
        $max_len_representative_name = 'max:' . HeadOfficeInfoConst::REPRESENTATIVE_NAME_MAX_LENGTH;
        $size_postal_code1 = 'size:' . HeadOfficeInfoConst::POSTAL_CODE1_MAX_LENGTH;
        $size_postal_code2 = 'size:' . HeadOfficeInfoConst::POSTAL_CODE2_MAX_LENGTH;
        $max_len_address1 = 'max:' . HeadOfficeInfoConst::ADDRESS1_MAX_LENGTH;
        $max_len_address2 = 'max:' . HeadOfficeInfoConst::ADDRESS2_MAX_LENGTH;
        $max_len_tel_number = 'max:' . HeadOfficeInfoConst::TEL_NUMBER_MAX_LENGTH;
        $max_len_fax_number = 'max:' . HeadOfficeInfoConst::FAX_NUMBER_MAX_LENGTH;
        $max_len_email = 'max:' . HeadOfficeInfoConst::EMAIL_MAX_LENGTH;
        $max_size_company_seal_image = 'max:' . HeadOfficeInfoConst::COMPANY_SEAL_IMAGE_MAX_SIZE;
        $max_len_invoice_number = 'max:' . HeadOfficeInfoConst::INVOICE_NUMBER_MAX_LENGTH;
        $max_len_bank_account = 'max:' . HeadOfficeInfoConst::ACCOUNT_NUMBER_MAX_LENGTH;

        // Validation纏め
        $company_name_validation_rule = ['bail', 'required', 'string', $max_len_company_name];
        $representative_name_validation_rule = ['bail', 'required', 'string', $max_len_representative_name];

        $postal_code1_validation_rule = ['bail', 'nullable', 'string', $size_postal_code1];
        $postal_code2_validation_rule = ['bail', 'nullable', 'string', $size_postal_code2];
        $address1_validation_rule = ['bail', 'required', 'string', $max_len_address1];
        $address2_validation_rule = ['bail', 'nullable', 'string', $max_len_address2];
        $tel_number_validation_rule = ['bail', 'nullable', 'string', $max_len_tel_number];
        $fax_number_validation_rule = ['bail', 'nullable', 'string', $max_len_fax_number];
        $email_validation_rule = ['bail', 'nullable', 'string', 'email', $max_len_email];
        $company_seal_image_validation_rule = ['bail', 'nullable', 'mimes:jpeg,png,gif', $max_size_company_seal_image];
        $invoice_number_validation_rule = ['bail', 'nullable', 'string', $max_len_invoice_number];
        $fiscal_year_validation_rule = ['bail', 'required', 'numeric', 'between:1,12'];
        $bank_account_validation_rule = ['bail', 'nullable', 'string', $max_len_bank_account];

        return [
            'company_name' => $company_name_validation_rule,
            'representative_name' => $representative_name_validation_rule,
            'postal_code1' => $postal_code1_validation_rule,
            'postal_code2' => $postal_code2_validation_rule,
            'address1' => $address1_validation_rule,
            'address2' => $address2_validation_rule,
            'tel_number' => $tel_number_validation_rule,
            'fax_number' => $fax_number_validation_rule,
            'tel_number2' => $tel_number_validation_rule,
            'email' => $email_validation_rule,
            'company_seal_image' => $company_seal_image_validation_rule,
            'invoice_number' => $invoice_number_validation_rule,
            'fiscal_year' => $fiscal_year_validation_rule,
            'bank_account1' => $bank_account_validation_rule,
            'bank_account2' => $bank_account_validation_rule,
            'bank_account3' => $bank_account_validation_rule,
            'bank_account4' => $bank_account_validation_rule,
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
            'company_name' => '会社名',
            'representative_name' => '代表者名',
            'postal_code1' => '郵便番号１',
            'postal_code2' => '郵便番号２',
            'address1' => '住所１',
            'address2' => '住所２',
            'tel_number' => '電話番号',
            'fax_number' => 'FAX番号',
            'tel_number2' => 'フリーダイヤル',
            'email' => 'メールアドレス',
            'company_seal_image' => '社印画像',
            'invoice_number' => 'インボイス登録番号',
            'fiscal_year' => '期首(会計開始月)',
            'bank_account1' => '振込先１',
            'bank_account2' => '振込先２',
            'bank_account3' => '振込先３',
            'bank_account4' => '振込先４',
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
