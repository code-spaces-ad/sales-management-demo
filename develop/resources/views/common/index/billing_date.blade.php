{{-- 請求日付Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('billing_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-2 pl-0 pb-md-3">
            <b>請求日付</b>
        </label>
        <div class="col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-12 px-0">
                <div class="col-md-4 input-tilde pl-0">

                    <input type="date" name="billing_date[start]" id="billing_date_start" value="{{ old('billing_date.start', $search_condition_input_data['billing_date']['start'] ?? '') }}"
                        class="form-control input-billing-date-start clear-value{{ $errors->has('billing_date.start') ? ' is-invalid' : '' }}" max="{{ $default_max_date }}"
                        onchange="clearInputMonth(this);">

                </div>
                <div class="col-md-4 pl-0 pl-md-1">
                    <input type="date" name="billing_date[end]" id="billing_date_end" value="{{ old('billing_date.end', $search_condition_input_data['billing_date']['end'] ?? '') }}"
                        class="form-control input-billing-date-end clear-value{{ $errors->has('billing_date.end') ? ' is-invalid' : '' }}" max="{{ $default_max_date }}"
                        onchange="clearInputMonth(this);">
                </div>
                {{-- 月次指定入力 --}}
                <div class="col-md-4 pl-0 pl-md-1 mt-2 mt-md-0">
                    <input type="month"
                           name="billing_month"
                           class="form-control input-month mr-2 clear-value"
                           id="input_month_billing"
                           max="{{ $default_max_month }}"
                           onchange="changeInputMonth(this, true);"
                           value="{{ $search_condition_input_data['billing_month'] ?? null }}">
                </div>
            </div>
            @error('billing_date.*')
            <div class="invalid-feedback ml-3">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
