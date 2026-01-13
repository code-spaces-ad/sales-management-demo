<?php

namespace App\Http\Requests\Master;

use App\Models\Master\MasterBranch;
use App\Models\Master\MasterCustomer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 納品先マスター検索用 リクエストクラス
 */
class RecipientSearchRequest extends FormRequest
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
            'recipient_name' => 'bail|nullable|string',
            'customer_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterCustomer())->getTable() . ',id'],
            'customer_id_code' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterCustomer())->getTable() . ',code'],
            'branch_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterBranch())->getTable() . ',id'],
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
            'recipient_name' => '納品先名',
            'customer_id' => '得意先ID',
            'customer_id_code' => '得意先コード',
            'branch_id' => '支所ID',
        ];
    }
}
