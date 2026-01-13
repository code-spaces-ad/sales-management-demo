{{-- 対象日付日付Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('target_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-4 col-form-label pl-0 pb-md-3">
            <b>対象日付</b>
            <span class="badge badge-danger">必須</span>
        </label>
        <div class="col-md-12 pr-md-0">
            <div class="d-md-inline-flex col-12 px-0">
                <div class="col-md-5 pl-0 pl-md-1">
                    <input type="datetime-local" name="target_date"
                           id="target_date"
                           value="{{ old('target_date', $search_condition_input_data['target_date'] ?? ($default_datetime_local ?? null)) }}"
                           class="form-control input-target-date clear-date-start{{ $errors->has('target_date') ? ' is-invalid' : '' }}"
                           max="{{ $default_max_date }}"
                           onchange="clearInputMonth(this);" >
                </div>
            </div>
            @error('target_date')
                <div class="invalid-feedback ml-3">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
