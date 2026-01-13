<?php

namespace App\Http\Requests\Master;

use App\Models\Master\MasterCustomer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 支所マスター検索用 リクエストクラス
 */
class BranchSearchRequest extends FormRequest
{
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
            'customer_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterCustomer())->getTable() . ',id'],
            'customer_id_code' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterCustomer())->getTable() . ',code'],
            'branch_name' => 'bail|nullable|string',
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
            'customer_id' => '得意先ID',
            'customer_id_code' => '得意先コード',
            'branch_name' => '支所名',
        ];
    }
}
