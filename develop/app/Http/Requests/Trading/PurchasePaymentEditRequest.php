<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Trading;

use App\Consts\DB\Sale\DepositOrderBillConst;
use App\Consts\DB\Trading\PaymentConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterTransactionType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 入金伝票作成編集用 リクエストクラス
 */
class PurchasePaymentEditRequest extends FormRequest
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
        $bill_date_validation_rule = ['bail', 'nullable', 'date'];
        $bill_number_validation_rule = ['bail', 'nullable', 'string', 'max:' . DepositOrderBillConst::BILL_NUMBER_MAX_LENGTH];
        if ($this->all()['amount_bill'] > 0) {
            // 手形入力あり時
            $bill_date_validation_rule = ['bail', 'required', 'date'];
        }

        return [
            'transaction_type_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterTransactionType())->getTable() . ',id', ],
            'order_date' => ['bail', 'required', 'date'],
            'supplier_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterSupplier())->getTable() . ',id,deleted_at,NULL', ],
            'department_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterDepartment())->getTable() . ',id',
                function ($attribute, $value, $fail) {
                    if ($value !== null) {
                        $officeFacilitiesId = request()->input('office_facilities_id');
                        if ($officeFacilitiesId === null) {
                            $fail(trans('validation.custom.department_check_office_id'));
                        }
                    }
                }],
            'office_facilities_id' => ['bail', 'required', 'integer',
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
            'note' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_MAX_LENGTH],
            'amount_cash' => ['bail', 'nullable', 'integer'],
            'amount_check' => ['bail', 'nullable', 'integer'],
            'amount_transfer' => ['bail', 'nullable', 'integer'],
            'amount_bill' => ['bail', 'nullable', 'integer'],
            'amount_offset' => ['bail', 'nullable', 'integer'],
            'amount_discount' => ['bail', 'nullable', 'integer'],
            'amount_fee' => ['bail', 'nullable', 'integer'],
            'amount_other' => ['bail', 'nullable', 'integer'],
            'note_cash' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'note_check' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'note_transfer' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'note_bill' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'note_offset' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'note_discount' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'note_fee' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'note_other' => ['bail', 'nullable', 'string', 'max:' . PaymentConst::NOTE_DETAIL_MAX_LENGTH],
            'deposit' => ['bail', 'nullable', 'integer'],
            'bill_date' => $bill_date_validation_rule,
            'bill_number' => $bill_number_validation_rule,
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
            'order_date' => '伝票日付',
            'supplier_id' => '仕入先',
            'note' => '備考',
            'amount_cash' => '金額_現金',
            'amount_check' => '金額_小切手',
            'amount_transfer' => '金額_振込',
            'amount_bill' => '金額_手形',
            'amount_offset' => '金額_相殺',
            'amount_discount' => '金額_値引',
            'amount_fee' => '金額_手数料',
            'amount_other' => '金額_その他',
            'note_cash' => '備考_現金',
            'note_check' => '備考_小切手',
            'note_transfer' => '備考_振込',
            'note_bill' => '備考_手形',
            'note_offset' => '備考_相殺',
            'note_discount' => '備考_値引',
            'note_fee' => '備考_手数料',
            'note_other' => '備考_その他',
            'deposit' => '合計',
            'bill_date' => '手形期日',
            'bill_number' => '手形番号',
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
