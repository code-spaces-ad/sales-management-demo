<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Sale;

use App\Consts\DB\Sale\SalesOrderDetailConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterTransactionType;
use App\Models\Master\MasterUnit;
use App\Models\Master\MasterWarehouse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 売上伝票作成編集用 リクエストクラス
 */
class OrderEditRequest extends FormRequest
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
        $dept_id_validation_rule = ['bail', 'nullable', 'integer', 'exists:' . with(new MasterWarehouse())->getTable() . ',id'];

        return [
            'transaction_type_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterTransactionType())->getTable() . ',id', ],
            'order_date' => ['bail', 'required', 'date'],
            'billing_date' => ['bail', 'required', 'date'],
            'customer_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterCustomer())->getTable() . ',id', ],
            'department_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterDepartment())->getTable() . ',id', ],
            'office_facilities_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterOfficeFacility())->getTable() . ',id', ],

            'branch_id' => ['bail', 'nullable', 'integer'],
            'recipient_id' => ['bail', 'nullable', 'integer'],
            'sales_total' => ['bail', 'required', 'integer'],
            'sales_total_normal_out' => ['bail', 'required', 'integer'],
            'sales_total_reduced_out' => ['bail', 'required', 'integer'],
            'sales_total_normal_in' => ['bail', 'required', 'integer'],
            'sales_total_reduced_in' => ['bail', 'required', 'integer'],
            'sales_total_free' => ['bail', 'required', 'integer'],
            'sales_tax_normal_out' => ['bail', 'required', 'integer'],
            'sales_tax_reduced_out' => ['bail', 'required', 'integer'],
            'sales_tax_normal_in' => ['bail', 'required', 'integer'],
            'sales_tax_reduced_in' => ['bail', 'required', 'integer'],
            'discount' => ['bail', 'required', 'numeric'],

            'detail' => ['bail', 'required', 'array', 'min:1'],
            'detail.*.product_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterProduct())->getTable() . ',id,deleted_at,NULL', ],
            'detail.*.product_name' => ['bail', 'required', 'string',
                'max:' . SalesOrderDetailConst::PRODUCT_NAME_MAX_LENGTH, ],
            'detail.*.quantity' => ['bail', 'required', 'numeric'],
            'detail.*.unit_name' => ['bail', 'nullable', 'string',
                'exists:' . with(new MasterUnit())->getTable() . ',name', ],
            'detail.*.unit_price' => ['bail', 'required', 'numeric'],
            'detail.*.discount' => ['bail', 'required', 'numeric'],
            'detail.*.tax' => ['bail', 'required', 'numeric'],
            'detail.*.tax_type_id' => ['bail', 'required', 'integer'],
            'detail.*.reduced_tax_flag' => ['bail', 'required', 'integer'],
            'detail.*.consumption_tax_rate' => ['bail', 'required', 'integer'],
            'detail.*.note' => ['bail', 'nullable', 'string',
                'max:' . SalesOrderDetailConst::NOTE_MAX_LENGTH, ],
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
            'order_date' => '伝票日付',
            'billing_date' => '請求日',
            'customer_id' => '得意先',
            'department_id' => '部門',
            'office_facilities_id' => '事業所',
            'branch_id' => '支所',
            'sales_total' => '売上合計',
            'sales_total_normal_out' => '今回売上額_通常税率_外税分',
            'sales_total_reduced_out' => '今回売上額_軽減税率_外税分',
            'sales_total_normal_in' => '今回売上額_通常税率_内税分',
            'sales_total_reduced_in' => '今回売上額_軽減税率_内税分',
            'sales_total_free' => '今回売上額_非課税分',
            'sales_tax_normal_out' => '消費税額_通常税率_外税分',
            'sales_tax_reduced_out' => '消費税額_軽減税率_外税分',
            'sales_tax_normal_in' => '消費税額_通常税率_内税分',
            'sales_tax_reduced_in' => '消費税額_軽減税率_内税分',
            'discount' => '値引額',

            'detail' => '商品',
            'detail.*.product_id' => '商品コード',
            'detail.*.product_name' => '商品名',
            'detail.*.quantity' => '数量',
            'detail.*.unit_name' => '単位',
            'detail.*.unit_price' => '単価',
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
