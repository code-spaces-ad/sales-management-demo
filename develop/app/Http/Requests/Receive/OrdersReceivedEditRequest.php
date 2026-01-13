<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Receive;

use App\Consts\DB\Receive\OrdersReceivedDetailConst;
use App\Http\Requests\Define\EditRequestTrait;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterProduct;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 受注伝票入力編集用 リクエストクラス
 */
class OrdersReceivedEditRequest extends FormRequest
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
            'order_date' => ['bail', 'required', 'date'],
            'customer_id' => ['bail', 'required', 'integer', 'exists:' . with(new MasterCustomer())->getTable() . ',id'],
            'branch_id' => ['bail', !is_null(request()->input('recipient_name')) ? 'required' : 'nullable', 'integer'],
            'recipient_name' => ['bail', 'nullable', 'string'],
            'recipient_name_kana' => ['bail', 'nullable', 'string'],
            'from_warehouse_id' => ['bail', 'nullable', 'integer'],
            'detail' => ['bail', 'required', 'array', 'min:1'],
            'detail.*.delivery_print' => ['bail', 'nullable', 'integer'],
            'detail.*.product_id' => ['bail', 'required', 'integer', 'exists:' . with(new MasterProduct())->getTable() . ',id,deleted_at,NULL'],
            'detail.*.warehouse_id' => ['bail',
                function ($attribute, $value, $fail) {
                    $no = explode('.', $attribute)[1];
                    $detail_delivery_date = request()->input('detail.' . $no . '.delivery_date');
                    if (is_null($value) && !is_null($detail_delivery_date)) {
                        return $fail('倉庫を選択してください。');
                    }
                },
            ],
            'detail.*.product_name' => ['bail', 'required', 'string', 'max:' . OrdersReceivedDetailConst::PRODUCT_NAME_MAX_LENGTH],
            'detail.*.quantity' => ['bail', 'required', 'numeric'],
            'detail.*.delivery_date' => ['bail', 'nullable', 'date'],
            'detail.*.note' => ['bail', 'nullable', 'string', 'max:' . OrdersReceivedDetailConst::NOTE_MAX_LENGTH],
            'detail.*.sales_confirm' => ['bail', 'nullable', 'integer'],
            'detail.*.sort' => ['bail', 'nullable', 'integer'],
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
            'order_date' => '受注日',
            'customer_id' => '得意先',
            'branch_id' => '支所',
            'recipient_name' => '納品先',
            'recipient_name_kana' => '納品先（かな）',
            'order_status' => '納品状況',
            'from_warehouse_id' => '移動元倉庫',
            'detail' => '商品',
            'detail.*.delivery_print' => '納品書印刷',
            'detail.*.product_id' => '商品コード',
            'detail.*.product_name' => '商品名',
            'detail.*.quantity' => '数量',
            'detail.*.delivery_date' => '納品日',
            'detail.*.warehouse_id' => '倉庫名',
            'detail.*.note' => '備考',
            'detail.*.sales_confirm' => '売上確定',
            'detail.*.sort' => 'ソート',
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
