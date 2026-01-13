<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterCustomersConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterHonorificTitle;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Master\MasterSummaryGroup;
use App\Rules\KanaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 得意先マスター作成編集用 リクエストクラス
 */
class CustomerEditRequest extends FormRequest
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
        $customer_id = $this->route()->parameter('customer')->id ?? null;

        // Validation定義
        $min_value_code = 'min:' . MasterCustomersConst::CODE_MIN_VALUE;
        $max_value_code = 'max:' . MasterCustomersConst::CODE_MAX_VALUE;
        $max_len_name = 'max:' . MasterCustomersConst::NAME_MAX_LENGTH;
        $max_len_name_kana = 'max:' . MasterCustomersConst::NAME_KANA_MAX_LENGTH;
        $size_postal_code1 = 'size:' . MasterCustomersConst::POSTAL_CODE1_MAX_LENGTH;
        $size_postal_code2 = 'size:' . MasterCustomersConst::POSTAL_CODE2_MAX_LENGTH;
        $max_len_address1 = 'max:' . MasterCustomersConst::ADDRESS1_MAX_LENGTH;
        $max_len_address2 = 'max:' . MasterCustomersConst::ADDRESS2_MAX_LENGTH;
        $max_len_tel_number = 'max:' . MasterCustomersConst::TEL_NUMBER_MAX_LENGTH;
        $max_len_fax_number = 'max:' . MasterCustomersConst::FAX_NUMBER_MAX_LENGTH;
        $max_len_email = 'max:' . MasterCustomersConst::EMAIL_MAX_LENGTH;
        $min_value_closing_date = 'min:' . MasterCustomersConst::CLOSING_DATE_MIN_VALUE;
        $max_value_closing_date = 'max:' . MasterCustomersConst::CLOSING_DATE_MAX_VALUE;
        $max_len_note = 'max:' . MasterCustomersConst::NOTE_MAX_LENGTH;

        return [
            'code' => [
                'bail', 'required', 'numeric', $min_value_code, $max_value_code,
                Rule::unique(with(new MasterCustomer())->getTable())
                    ->ignore($customer_id)->whereNull('deleted_at'),
            ],
            'name' => ['bail', 'required', 'string', $max_len_name],
            //            'name_kana' => ['bail', 'nullable', 'string', $max_len_name_kana, new KanaRule()],
            'name_kana' => ['bail', 'nullable', 'string', $max_len_name_kana],
            'honorific_title_id' => [
                'bail', 'required', 'integer',
                'exists:' . with(new MasterHonorificTitle())->getTable() . ',id',
            ],
            'postal_code1' => ['bail', 'nullable', 'string', $size_postal_code1],
            'postal_code2' => ['bail', 'nullable', 'string', $size_postal_code2],
            'address1' => ['bail', 'required', 'string', $max_len_address1],
            'address2' => ['bail', 'nullable', 'string', $max_len_address2],
            'tel_number' => ['bail', 'nullable', 'string', $max_len_tel_number],
            'fax_number' => ['bail', 'nullable', 'string', $max_len_fax_number],
            'email' => ['bail', 'nullable', 'string', 'email', $max_len_email],
            'tax_calc_type_id' => ['bail', 'required', 'integer'],
            'tax_rounding_method_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterRoundingMethod())->getTable() . ',id,deleted_at,NULL', ],
            'transaction_type_id' => ['bail', 'required', 'integer'],
            'closing_date' => ['bail', 'required', 'integer', $min_value_closing_date, $max_value_closing_date],
            'billing_customer_id' => [
                'bail', 'nullable', 'integer',
                'exists:' . with(new MasterCustomer())->getTable() . ',id',
            ],
            'start_account_receivable_balance' => ['bail', 'nullable', 'integer'],
            'collection_month' => ['bail', 'required', 'integer'],
            'collection_day' => ['bail', 'required', 'integer'],
            'collection_method' => ['bail', 'required', 'integer'],
            'sales_invoice_format_type' => ['bail', 'required', 'integer'],
            'sales_invoice_printing_method' => ['bail', 'required', 'integer'],
            'sort_code' => [
                'bail', 'required', 'numeric', $min_value_code, $max_value_code,
                Rule::unique(with(new MasterCustomer())->getTable())
                    ->ignore($customer_id)->whereNull('deleted_at'),
            ],
            'employee_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterEmployee())->getTable() . ',id'],
            'summary_group_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterSummaryGroup())->getTable() . ',id'],
            'department_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterDepartment())->getTable() . ',id', ],
            'office_facilities_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterOfficeFacility())->getTable() . ',id', ],
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
            'code' => '得意先コード',
            'name' => '得意先名',
            'name_kana' => '得意先名かな',
            'honorific_title_id' => '敬称',
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
            'closing_date' => '請求締日',
            'billing_customer_id' => '請求先',
            'start_account_receivable_balance' => '開始売掛残高',
            'collection_month' => '回収月',
            'collection_day' => '回収日',
            'collection_method' => '回収方法',
            'sales_invoice_format_type' => '請求書書式',
            'sales_invoice_printing_method' => '印刷方式',
            'sort_code' => 'ソート番号',
            'employee_id' => '担当者',
            'summary_group_id' => '集計グループ',
            'department_id' => '部門',
            'office_facilities_id' => '事業所',
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
