<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterSubCategoriesConst;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterSubCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * サブカテゴリマスター作成編集用 リクエストクラス
 */
class SubCategoryEditRequest extends FormRequest
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
        // コード値の確認
        $table = with(new MasterSubCategory())->getTable();    // テーブル名
        $category_id = $this->category_id;
        $sub_category_id = $this->route()->parameter('sub_category')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($sub_category_id, 'code')
            ->where('category_id', $category_id)
            ->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterSubCategoriesConst::CODE_MIN_VALUE,
                'max:' . MasterSubCategoriesConst::CODE_MAX_VALUE, $unique_code],
            'category_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterCategory())->getTable() . ',id', ],
            'name' => ['bail', 'required', 'string', 'max:' . MasterSubCategoriesConst::NAME_MAX_LENGTH],
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
            'category_id' => 'カテゴリー',
            'name' => 'サブカテゴリ名',
        ];
    }
}
