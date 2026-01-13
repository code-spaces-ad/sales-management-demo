<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterProductsConst;
use App\Models\Master\MasterAccountingCode;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterClassification1;
use App\Models\Master\MasterClassification2;
use App\Models\Master\MasterKind;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Master\MasterSection;
use App\Models\Master\MasterSubCategory;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUnit;
use App\Rules\KanaRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ユーザーマスター作成編集用 リクエストクラス
 */
class ProductEditRequest extends FormRequest
{
    /**
     * Determine if the product is authorized to make this request.
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
            'code' => ['bail', 'required', 'numeric',
                'min:' . MasterProductsConst::CODE_MIN_VALUE, 'max:' . MasterProductsConst::CODE_MAX_VALUE,
                Rule::unique(with(new MasterProduct())->getTable())
                    ->ignore($this->route()->parameter('product')->id ?? null),
            ],
            'name' => ['bail', 'required', 'string', 'max:' . MasterProductsConst::NAME_MAX_LENGTH],
            //            'name_kana' => ['bail', 'nullable', 'string',
            //                'max:' . MasterProductsConst::NAME_KANA_MAX_LENGTH, new KanaRule()],
            'name_kana' => ['bail', 'nullable', 'string', 'max:' . MasterProductsConst::NAME_KANA_MAX_LENGTH],
            'customer_product_code' => ['bail', 'nullable', 'string',
                'max:' . MasterProductsConst::CUSTOMER_PRODUCT_CODE_MAX_LENGTH],
            'jan_code' => ['bail', 'nullable', 'string', 'max:' . MasterProductsConst::JAN_CODE_MAX_LENGTH],
            'category_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterCategory())->getTable() . ',id', ],
            'sub_category_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterSubCategory())->getTable() . ',id', ],
            'unit_price' => ['bail', 'nullable', 'numeric'],
            'purchase_unit_price' => ['bail', 'nullable', 'numeric'],
            'unit_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterUnit())->getTable() . ',id', ],
            'unit_price_decimal_digit' => ['bail', 'nullable', 'numeric',
                'min:' . MasterProductsConst::UNIT_PRICE_DECIMAL_DIGIT_MIN_VALUE,
                'max:' . MasterProductsConst::UNIT_PRICE_DECIMAL_DIGIT_MAX_VALUE, ],
            'quantity_decimal_digit' => ['bail', 'nullable', 'numeric',
                'min:' . MasterProductsConst::QUANTITY_DECIMAL_DIGIT_MIN_VALUE,
                'max:' . MasterProductsConst::QUANTITY_DECIMAL_DIGIT_MAX_VALUE, ],
            'tax_type_id' => ['bail', 'required', 'integer'],
            'reduced_tax_flag' => ['bail', 'required', 'integer'],
            'quantity_rounding_method_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterRoundingMethod())->getTable() . ',id,deleted_at,NULL', ],
            'amount_rounding_method_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterRoundingMethod())->getTable() . ',id,deleted_at,NULL', ],
            'accounting_code' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterAccountingCode())->getTable() . ',id', ],
            'supplier_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterSupplier())->getTable() . ',id,deleted_at,NULL', ],
            'note' => ['bail', 'nullable', 'string', 'max:' . MasterProductsConst::NOTE_MAX_LENGTH],
            'specification' => ['bail', 'nullable', 'string', 'max:' . MasterProductsConst::SPECIFICATION_MAX_LENGTH],
            'kind_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterKind())->getTable() . ',id,deleted_at,NULL', ],
            'section_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterSection())->getTable() . ',id,deleted_at,NULL', ],
            'purchase_unit_weight' => ['bail', 'nullable', 'numeric'],
            'classification1_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterClassification1())->getTable() . ',id,deleted_at,NULL', ],
            'classification2_id' => ['bail', 'nullable', 'integer',
                'exists:' . with(new MasterClassification2())->getTable() . ',id,deleted_at,NULL', ],
            'product_status' => ['bail', 'nullable', 'integer'],
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
            'code' => '商品コード',
            'name' => '商品名',
            'name_kana' => '商品名カナ',
            'customer_product_code' => '相手先商品コード',
            'jan_code' => 'JANコード',
            'category_id' => 'カテゴリー',
            'sub_category_id' => 'サブカテゴリー',
            'unit_price' => '単価',
            'purchase_unit_price' => '仕入単価',
            'unit_id' => '単位',
            'unit_price_decimal_digit' => '単価小数桁数',
            'quantity_decimal_digit' => '数量小数桁数',
            'tax_type_id' => '税区分',
            'quantity_rounding_method_id' => '数量端数処理',
            'amount_rounding_method_id' => '単価端数処理',
            'accounting_code' => '経理コード',
            'supplier_id' => '仕入先',
            'note' => '備考',
            'specification' => '仕様',
            'kind_id' => '種別',
            'section_id' => '管理部署',
            'purchase_unit_weight' => '単重',
            'classification1_id' => '分類１',
            'classification2_id' => '分類２',
            'product_status' => '商品区分',
        ];
    }
}
