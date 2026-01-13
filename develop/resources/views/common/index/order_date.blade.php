{{-- 伝票日付Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('order_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-2 pl-0 pb-md-3">
            <b>伝票日付</b>
        </label>
        <div class="col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-12 px-0">
                <div class="col-md-4 input-tilde pl-0">

                    <input type="date" name="order_date[start]" id="order_date_start"  value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? null) }}"
                        class="form-control input-order-date-start clear-date-start{{ $errors->has('order_date.start') ? ' is-invalid' : '' }}" max="{{ $default_max_date }}" onchange="clearInputMonth(this);" >

                </div>
                <div class="col-md-4 pl-0 pl-md-1">

                    <input type="date" name="order_date[end]" id="order_date_end" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? null) }}"
                        class="form-control input-order-date-end clear-date-end{{ $errors->has('order_date.end') ? ' is-invalid' : '' }}" max="{{ $default_max_date }}" onchange="clearInputMonth(this);" />

                </div>
                {{-- 月次指定入力 --}}
                <div class="col-md-4 pl-0 pl-md-1 mt-2 mt-md-0">
                    <input type="month"
                           name="order_month"
                           class="form-control input-month mr-2 clear-value"
                           id="input_month_order"
                           max="{{ $default_max_month }}"
                           onchange="changeInputMonth(this);"
                           value="{{ old('order_date.order_month', $search_condition_input_data['order_month'] ?? null) }}">
                </div>
            </div>
            @error('order_date.*')
            <div class="invalid-feedback ml-3">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
