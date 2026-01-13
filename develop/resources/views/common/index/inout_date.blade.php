{{-- 入出庫日Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('order_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-2 pl-0 pb-md-3">
            <b>入出庫日</b>
        </label>
        <div class="col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-12 px-0">
                <div class="col-md-4 input-tilde pl-0">

                    <input type="date" name="inout_date[start]" id="inout_date_start" value="{{ old('inout_date.start', $search_condition_input_data['inout_date']['start'] ?? '') }}"
                        class="form-control input-inout-date-start clear-date-start{{ $errors->has('inout_date.start') ? ' is-invalid' : '' }}" onchange="clearInputMonth(this);">

                </div>
                <div class="col-md-4 pl-0 pl-md-1">
                    <input type="date" name="inout_date[end]" id="inout_date_end" value="{{ old('inout_date.end', $search_condition_input_data['inout_date']['end'] ?? '') }}"
                        class="form-control input-inout-date-end clear-date-end{{ $errors->has('inout_date.end') ? ' is-invalid' : '' }}" onchange="clearInputMonth(this);">

                </div>
                {{-- 月次指定入力 --}}
                <div class="col-md-4 pl-0 pl-md-1 mt-2 mt-md-0">
                    <input type="month"
                           name="order_month"
                           class="form-control input-month mr-2 clear-value"
                           id="input_month_order"
                           max="{{ $default_max_month }}"
                           onchange="changeInputMonth(this);"
                           value="{{ $search_condition_input_data['order_month'] ?? null }}">
                </div>
            </div>
            @error('inout_date.*')
            <div class="invalid-feedback ml-3">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
