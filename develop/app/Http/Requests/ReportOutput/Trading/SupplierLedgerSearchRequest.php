<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\ReportOutput\Trading;

use App\Http\Requests\Define\SearchRequestTrait;
use App\Models\Master\MasterSupplier;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SupplierLedgerSearchRequest extends FormRequest
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
        $table_supplier = with(new MasterSupplier())->getTable();    // テーブル名
        $exists_supplier = "exists:{$table_supplier},id";

        $add_rules = [
            'supplier_id' => ['bail', 'nullable', 'integer', $exists_supplier],
        ];

        // 共通化しているルール + $add_rulesに指定したルールをreturn
        return array_merge_recursive($this->setRulesArray(), $add_rules);
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes(): array
    {
        $add_attributes = [
            'supplier_id' => '仕入先ID',
        ];

        // 共通化しているアトリビュート + $add_attributesに指定したアトリビュートをreturn
        return array_merge_recursive($this->setAttributesArray(), $add_attributes);
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
            'supplier_id' => MasterSupplier::query()->orderBy('name_kana')->value('id'),
        ];
    }
}
