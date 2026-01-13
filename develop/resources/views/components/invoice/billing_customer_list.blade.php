@props(['search_result'])
@props(['search_condition_input_data'])
@foreach ($search_result['charge_data'] ?? [] as $key => $detail)
    <tr id="customer_{{ $detail['id'] }}">
        {{-- 締対象チェックボックス --}}
        <td class="text-center">
            @if (count($detail->featureCharges) > 0)
                -
            @else
                <div class="custom-form-check">
                    <input type="checkbox" name="target_charge_closing[{{ $key }}]"
                           value="{{ $detail['id'] }}"
                           id="target_charge_closing_{{ $key }}"
                           class="form-check-input input-target-charge-closing"
                        {{ old("target_charge_closing[$key]", true) ? 'checked' : '' }}>
                </div>
            @endif
        </td>
        <td style="display: none;">
            @if (count($detail->charges) > 0)
                {{ $detail->charges[0]->id }}
            @endif
        </td>
        <td style="display: none;">
            {{-- 当月より未来に締データがあるか --}}
            {{ count($detail->featureCharges) > 0 ? 1 : 0 }}
        </td>
        <td class="text-left" data-title="得意先">
            @if (count($detail->charges) > 0)
                <div class="btn btn-success btn-xs pt-2 pb-2 pl-2 pr-2">締処理済</div>
                <a href="{{ route('invoice.charge_detail.index',
                    [
                        'customer_id' => $detail['id'],
                        'charge_date' => $search_condition_input_data['charge_date'] ?? '',
                        'closing_date' => $search_condition_input_data['closing_date'] ?? '',
                    ]) }}">
                    {{ StringHelper::getNameWithId($detail['code_zerofill'], $detail['name']) }}
                </a>
                @if($detail->charges[0]->sales_order_count !== count($detail->ClosingSalesOrder))
                    <div class="invalid-feedback">
                        <b>
                            <i class="fas fa-exclamation-circle"></i>
                            対象期間内に未処理の売上伝票が
                            {{ count($detail->ClosingSalesOrder) - $detail->charges[0]->sales_order_count }}
                            件あります。
                        </b>
                    </div>
                @endif
                @if($detail->charges[0]->deposit_order_count !== count($detail->ClosingDepositOrder))
                    <div class="invalid-feedback">
                        <b>
                            <i class="fas fa-exclamation-circle"></i>
                            対象期間内に未処理の入金伝票が
                            {{ count($detail->ClosingDepositOrder) - $detail->charges[0]->deposit_order_count }}
                            件あります。
                        </b>
                    </div>
                @endif
            @else
                {{ StringHelper::getNameWithId($detail['code_zerofill'], $detail['name']) }}
            @endif
        </td>
        <td class="text-left" data-title="部門">
            @if (isset($detail->SalesOrder[0]))
                {{ $detail->SalesOrder[0]->department_name }}
            @else
                -
            @endif
        </td>
        <td class="text-left" data-title="事業所">
            @if (isset($detail->SalesOrder[0]))
                {{ $detail->SalesOrder[0]->office_facilities_name }}
            @else
                -
            @endif
        </td>
        <td class="text-left" data-title="締実施者">
            @if (count($detail->charges) > 0)
                {{ $detail->charges[0]->closing_user_name }}
            @else
                -
            @endif
        </td>
        <td class="text-left" data-title="締処理日時">
            @if (count($detail->charges) > 0)
                {{ $detail->charges[0]->created_at }}
            @else
                -
            @endif
        </td>
        <td class="text-right" data-title="売掛金額">
            @if (count($detail->charges) > 0)
                {{ number_format($detail->charges[0]->sales_total) }}
            @else
                -
            @endif
        </td>
        <td class="text-right" data-title="消費税額">
            @if (count($detail->charges) > 0)
                {{ number_format($detail->charges[0]->sales_tax_total) }}
            @else
                -
            @endif
        </td>
        <td class="text-center" data-title="売掛">
            @if (count($detail->charges) > 0)
                {{ number_format($detail->charges[0]->sales_order_count) }}
            @else
                {{ number_format(count($detail->ClosingSalesOrder)) }}
            @endif
        </td>
        <td class="text-center" data-title="入金">
            @if (count($detail->charges) > 0)
                {{ number_format($detail->charges[0]->deposit_order_count) }}
            @else
                {{ number_format(count($detail->ClosingDepositOrder)) }}
            @endif
        </td>
        <td class="text-center" data-title="個別">
            @if (count($detail->featureCharges) > 0)
                -
            @else
                @if (count($detail->charges) != 0)
                    <button type="button" name="single-cancel" class="btn btn-danger btn-xs"
                            onclick="chargeClosingCancelSingle('{{ $detail->charges[0]->id }}','{{ $detail['name'] }}');">
                        <div class="spinner-border text-light" role="status"
                             style="display: none;"></div>
                        解除
                    </button>
                @else
                    <button type="button" name="single-closing" class="btn btn-primary btn-xs"
                            onclick="chargeClosingStoreSingle('{{ $detail['id'] }}','{{ $detail['name'] }}');">
                        <div class="spinner-border text-light" role="status"
                             style="display: none;"></div>
                        締処理
                    </button>
                @endif
            @endif
        </td>
        <td class="text-center" data-title="帳票" style="display: none">
            @if (count($detail->charges) != 0)
                <button type="button" name="single-print" class="btn btn-primary btn-xs"
                        onclick="">
                    請求書
                </button>
            @else - @endif
        </td>

        <input type="hidden" name="detail[{{ $key }}][charge_data_id]" value="{{ $detail['id'] }}">
    </tr>
@endforeach
