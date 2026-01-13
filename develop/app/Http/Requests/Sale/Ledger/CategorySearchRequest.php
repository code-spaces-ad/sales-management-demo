<?php

namespace App\Http\Requests\Sale\Ledger;

use App\Http\Requests\Define\SearchRequestTrait;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CategorySearchRequest extends FormRequest
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

    /**
     * デフォルトセット
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'order_date' => [
                'start' => Carbon::now()->startOfMonth()->toDateString(),
                'end' => Carbon::now()->endOfMonth()->toDateString(),
            ],
        ];
    }
}
