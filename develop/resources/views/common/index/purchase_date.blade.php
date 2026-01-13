{{-- 入荷日Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('purchase_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-2 pl-0 pb-md-3">
            <b>入荷日</b>
        </label>
        <div class="col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-12 px-0">
                <div class="col-md-4 input-tilde pl-0">
                    <input type="date"
                           name="purchase_date[start]"
                           id="purchase_date_start"
                           value="{{ old('purchase_date.start', $search_condition_input_data['purchase_date']['start'] ?? null) }}"
                           class="form-control input-purchase-date-start clear-date-today-start{{ $errors->has('purchase_date.start') ? ' is-invalid' : '' }}"
                           max="{{ config('consts.default.common.default_max_month') }}"
                           onchange="clearInputMonth(this);" >
                </div>
                <div class="col-md-4 pl-0 pl-md-1">
                    <input type="date"
                           name="purchase_date[end]"
                           id="purchase_date_end"
                           value="{{ old('purchase_date.end', $search_condition_input_data['purchase_date']['end'] ?? null) }}"
                           class="form-control input-purchase-date-end clear-date-today-end{{ $errors->has('purchase_date.end') ? ' is-invalid' : '' }}"
                           max="{{ config('consts.default.common.default_max_month') }}"
                           onchange="clearInputMonth(this);" />
                </div>
                {{-- 月次指定入力 --}}
                <div class="col-md-4 pl-0 pl-md-1 mt-2 mt-md-0">
                    <input type="month"
                           name="purchase_month"
                           class="form-control input-month mr-2 clear-value"
                           id="input_month_purchase"
                           max="{{ config('consts.default.common.default_max_month') }}"
                           onchange="changeInputMonth(this);"
                           value="{{ old('purchase_date.purchase_month', $search_condition_input_data['purchase_month'] ?? null) }}">
                </div>
            </div>
            @error('purchase_date.*')
                <div class="invalid-feedback ml-3">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
