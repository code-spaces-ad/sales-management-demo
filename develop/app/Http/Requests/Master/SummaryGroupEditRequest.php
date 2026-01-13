<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterSummaryGroupConst;
use App\Models\Master\MasterSummaryGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 集計グループマスター作成編集用 リクエストクラス
 */
class SummaryGroupEditRequest extends FormRequest
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
        $table = with(new MasterSummaryGroup())->getTable();    // テーブル名
        $summary_group_id = $this->route()->parameter('summary_group')->code ?? null;
        $unique_code = Rule::unique($table)->ignore($summary_group_id, 'code')->whereNull('deleted_at');

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterSummaryGroupConst::CODE_MIN_VALUE,
                'max:' . MasterSummaryGroupConst::CODE_MAX_VALUE, $unique_code],
            'name' => ['bail', 'required', 'string', 'max:' . MasterSummaryGroupConst::NAME_MAX_LENGTH],
            'note' => ['bail', 'nullable', 'string', 'max:' . MasterSummaryGroupConst::NOTE_MAX_LENGTH],
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
            'name' => '集計グループ名',
            'note' => '備考',
        ];
    }
}
