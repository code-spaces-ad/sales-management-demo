<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterCustomerPriceConst;
use App\Models\Master\MasterCustomerPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 得意先別単価マスター作成編集用 リクエストクラス
 */
class CustomerPriceEditRequest extends FormRequest
{
    public mixed $customer_price;

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
        $code = $this->route()->parameter('customer_price')->code ?? null;
        $id_validation_rule = ['bail', 'integer'];

        return [
            'code' => ['bail',  'string',
                'max:' . MasterCustomerPriceConst::CODE_MAX_LENGTH,
                Rule::unique(with(new MasterCustomerPrice())->getTable())
                    ->ignore($code)->whereNull('deleted_at')],
            'product_id' => $id_validation_rule,
            'customer_id' => $id_validation_rule,
            'sales_date' => ['bail', 'nullable'],
            'sales_unit_price' => ['bail',  'numeric'],
            'tax_included' => ['bail',  'numeric', 'max:' . MasterCustomerPriceConst::TAX_INCLUDED_MAX_LENGTH],
            'reduced_tax_included' => ['bail',  'numeric', 'max:' . MasterCustomerPriceConst::REDUCED_TAX_INCLUDED_MAX_LENGTH],
            'unit_price' => ['bail', 'numeric', 'max:' . MasterCustomerPriceConst::UNIT_PRICE_MAX_LENGTH],
            'note' => ['bail', 'nullable', 'string', 'max:' . MasterCustomerPriceConst::NOTE_MAX_LENGTH],
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
            'code' => 'コード値',
            'product_id' => '商品ID',
            'customer_id' => '得意先ID',
            'sales_unit_price' => '最終売上単価',
            'sales_date' => '最終売上日',
            'tax_included' => '通常税率_税込単価',
            'reduced_tax_included' => '軽減税率_税込単価',
            'unit_price' => '税抜単価',
            'note' => '備考',
        ];
    }
}
