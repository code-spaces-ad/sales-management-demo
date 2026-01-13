<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Sale\Ledger;

use App\Http\Requests\Define\SearchRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 金種別入金一覧検索用 リクエストクラス
 */
class DepositsSearchRequest extends FormRequest
{
    use SearchRequestTrait;

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
        return $this->setRulesArray();
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes(): array
    {
        return $this->setAttributesArray();
    }
}
