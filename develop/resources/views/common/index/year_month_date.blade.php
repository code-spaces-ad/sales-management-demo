{{-- 年月度Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('year_month_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-2 pl-0 pb-md-3">
            <b>年月度</b>
        </label>
        <div class="col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-12 px-0">
                <div class="col-md-4 pl-0">
                    <input type="month"
                           class="form-control input-month"
                           id="year_month"
                           name="year_month"
                           value="{{ old('year_month', $search_condition_input_data['year_month'] ?? null) }}"
                           max="{{ config('consts.default.common.default_max_month') }}">
                </div>
                @error('year_month')
                    <div class="invalid-feedback ml-3">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
@show
