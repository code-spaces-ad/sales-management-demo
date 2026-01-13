<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\PurchaseInvoice;

use App\Enums\OrderStatus;
use App\Models\Master\MasterEmployee;
use App\Rules\EnumValueCustom;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 請求書発行検索用 リクエストクラス
 */
class InvoicePrintSearchRequest extends FormRequest
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
        $table_employee = with(new MasterEmployee())->getTable();
        $exists_employee = "exists:{$table_employee},id";

        $order_date_validation_rule = ['bail', 'nullable', 'array'];
        $order_date_start_validation_rule = ['bail', 'nullable', 'date'];
        $order_date_end_validation_rule = ['bail', 'nullable', 'date', 'after_or_equal:order_date.start'];

        $employee_id_validation_rule = ['bail', 'nullable', 'integer', $exists_employee];

        $order_status_list_validation_rule = ['bail', 'nullable', 'array'];
        $order_status_validation_rule = [
            'bail',
            'nullable',
            'integer',
            new EnumValueCustom(OrderStatus::class, false),
        ];

        return [
            'order_date' => $order_date_validation_rule,
            'order_date.start' => $order_date_start_validation_rule,
            'order_date.end' => $order_date_end_validation_rule,
            'employee_id' => $employee_id_validation_rule,
            'order_status' => $order_status_list_validation_rule,
            'order_status.*' => $order_status_validation_rule,
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
            'order_date' => '発注日',
            'order_date.start' => '発注日（開始日）',
            'order_date.end' => '発注日（終了日）',
            'employee_id' => '担当者',
            'order_status' => '状態',
            'order_status.*' => '状態',
        ];
    }
}
