<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Invoice;

use App\Http\Requests\Define\EditRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 請求締処理用 リクエストクラス
 */
class ChargeClosingStoreRequest extends FormRequest
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
            'customer_ids' => ['bail', 'required', 'string'],
            'charge_date' => ['bail', 'required', 'string'],
            'closing_date' => ['bail', 'required', 'int'],
            'department_id' => ['bail', 'nullable', 'int'],
            'office_facility_id' => ['bail', 'nullable', 'int'],
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
            'customer_ids' => '得意先IDS',
            'charge_date' => '締処理年月',
            'closing_date' => '締処理区分',
            'department_id' => '部門',
            'office_facility_id' => '事業所',
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
