<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterCategoriesConst;
use App\Models\Master\MasterCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ユーザーマスター作成編集用 リクエストクラス
 */
class CategoryEditRequest extends FormRequest
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
        $table = with(new MasterCategory())->getTable();    // テーブル名
        $category_id = $this->route()->parameter('category')->code ?? null;

        $min_value_code = 'min:' . MasterCategoriesConst::CODE_MIN_VALUE;
        $max_value_code = 'max:' . MasterCategoriesConst::CODE_MAX_VALUE;
        $unique_code = Rule::unique($table)->ignore($category_id, 'code')->whereNull('deleted_at');

        return [
            'code' => [
                'bail', 'required', 'numeric', $min_value_code, $max_value_code, $unique_code,
            ],
            'name' => ['bail', 'required', 'string'],
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
            'code' => 'コード',
            'name' => 'カテゴリー名',
        ];
    }
}
