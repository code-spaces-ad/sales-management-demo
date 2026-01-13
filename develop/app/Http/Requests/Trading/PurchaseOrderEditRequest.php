<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Trading;

use App\Consts\DB\Trading\PurchaseOrderDetailConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterTransactionType;
use App\Models\Master\MasterUnit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 発注入力編集用 リクエストクラス
 */
class PurchaseOrderEditRequest extends FormRequest
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
        return [
            'transaction_type_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterTransactionType())->getTable() . ',id', ],
            'order_date' => ['bail', 'required', 'date'],
            'closing_date' => ['bail', 'required', 'date'],
            'supplier_id' => ['bail', 'required', 'integer', 'exists:' . with(new MasterSupplier())->getTable() . ',id'],
            'department_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterDepartment())->getTable() . ',id', ],
            'office_facilities_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterOfficeFacility())->getTable() . ',id', ],
            'order_status' => [],
            'purchase_total' => ['bail', 'required', 'integer'],
            'purchase_total_normal_out' => ['bail', 'required', 'integer'],
            'purchase_total_reduced_out' => ['bail', 'required', 'integer'],
            'purchase_total_normal_in' => ['bail', 'required', 'integer'],
            'purchase_total_reduced_in' => ['bail', 'required', 'integer'],
            'purchase_total_free' => ['bail', 'required', 'integer'],
            'purchase_tax_normal_out' => ['bail', 'required', 'integer'],
            'purchase_tax_reduced_out' => ['bail', 'required', 'integer'],
            'purchase_tax_normal_in' => ['bail', 'required', 'integer'],
            'purchase_tax_reduced_in' => ['bail', 'required', 'integer'],
            'discount' => ['bail', 'required', 'numeric'],

            'detail' => ['bail', 'required', 'array', 'min:1'],
            'detail.*.product_name' => ['bail', 'required', 'string', 'max:' . PurchaseOrderDetailConst::PRODUCT_NAME_MAX_LENGTH],
            'detail.*.quantity' => ['bail', 'required', 'numeric'],
            'detail.*.unit_name' => ['bail', 'nullable', 'string', 'exists:' . with(new MasterUnit())->getTable() . ',name'],
            'detail.*.unit_price' => ['bail', 'required', 'numeric'],
            'detail.*.discount' => ['bail', 'required', 'numeric'],
            'detail.*.tax' => ['bail', 'required', 'numeric'],
            'detail.*.tax_type_id' => ['bail', 'required', 'integer'],
            'detail.*.reduced_tax_flag' => ['bail', 'required', 'integer'],
            'detail.*.consumption_tax_rate' => ['bail', 'required', 'integer'],
            'detail.*.note' => ['bail', 'nullable', 'string', 'max:' . PurchaseOrderDetailConst::NOTE_MAX_LENGTH],
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
            'transaction_type_id' => '伝票種別',
            'order_date' => '仕入日',
            'closing_date' => '締日',
            'supplier_id' => '仕入先',
            'department_id' => '部門',
            'office_facilities_id' => '事業所',
            'purchase_total' => '仕入合計',
            'purchase_total_normal_out' => '今回仕入額_通常税率_外税分',
            'purchase_total_reduced_out' => '今回仕入額_軽減税率_外税分',
            'purchase_total_normal_in' => '今回仕入額_通常税率_内税分',
            'purchase_total_reduced_in' => '今回仕入額_軽減税率_内税分',
            'purchase_total_free' => '今回仕入額_非課税分',
            'purchase_tax_normal_out' => '消費税額_通常税率_外税分',
            'purchase_tax_reduced_out' => '消費税額_軽減税率_外税分',
            'purchase_tax_normal_in' => '消費税額_通常税率_内税分',
            'purchase_tax_reduced_in' => '消費税額_軽減税率_内税分',
            'discount' => '値引額',

            'detail' => '商品',
            'detail.*.product_name' => '商品名',
            'detail.*.quantity' => '数量',
            'detail.*.unit_name' => '単位',
            'detail.*.unit_price' => '単価',
            'detail.*.discount' => '値引額',
            'detail.*.tax' => '税額',
            'detail.*.tax_type_id' => '税区分',
            'detail.*.consumption_tax_rate' => '消費税率',
            'detail.*.note' => '備考',
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
