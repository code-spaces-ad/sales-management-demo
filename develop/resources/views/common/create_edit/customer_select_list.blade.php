{{-- 得意先リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('customer_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-3 col-form-label pl-0 pb-md-3">
            <b>得意先</b>
            <span class="badge badge-danger">必須</span>
        </label>
        <div class="flex-md-column col-md-9 pr-md-0 pl-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 得意先コード（セレクトボックス選択用） --}}
                <input type="number" id="customer_code" oninput="inputCode(this);"
                    class="form-control input-customer-code h-75 col-5 col-md-4 mr-md-1"
                    onchange="changeCustomerCodeCreateEdit(this);" maxlength="{{ $maxlength_customer_code }}" min="{{ $min_customer_code }}"
                    value="{{ $target_record_data->customer_code_zero_fill }}" placeholder="得意先コード">
                {{-- 得意先1 --}}
                <select name="customer_id" onchange="changeCustomerCreateEdit();"
                        class="custom-select input-customer-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('customer_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['customers'] ?? []) as $item)
                        <option
                                @if ($item['id'] == old('customer_id', $target_record_data['customer_id'] ?? null))
                                    selected
                                @endif
                                value="{{ $item['id'] }}"
                                data-code="{{ $item['code'] }}"
                                data-name="{{ $item['name'] }}"
                                data-name-kana="{{ $item['name_kana'] }}"
                                data-tax-calc-type="{{ $item['mBillingCustomer']['tax_calc_type_id'] }}"
                                data-tax-rounding-method="{{ $item['tax_rounding_method_id'] }}"
                                data-transaction-type-id="{{ $item['transaction_type_id'] }}">
                            {{ $item['name'] }} ({{ \App\Enums\TaxCalcType::asSelectArray()[$item['tax_calc_type_id']] }} - {{ \App\Enums\RoundingMethodType::asSelectArray()[$item['tax_rounding_method_id']] }})
                        </option>
                    @endforeach
                </select>
            </div>
            @error('customer_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
