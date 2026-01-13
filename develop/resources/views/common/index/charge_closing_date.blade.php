{{-- 締年月日(請求)Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('charge_closing_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-2 pl-0 pb-md-3">
            <b>締年月日</b>
        </label>
        <div class="col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-12 px-0">
                <div class="col-md-4 pl-0">
                    <input type="month"
                           class="form-control input-month"
                           id="charge_date"
                           name="charge_date"
                           value="{{ $search_condition_input_data['charge_date'] ?? null }}"
                           max="{{ config('consts.default.common.default_max_month') }}">
                </div>
                <div class="col-md-4 pl-0">
                    <select id="closing_date" name="closing_date"
                            class="custom-select input-closing-date clear-select
                            @if($errors->has('closing_date')) is-invalid @endif">
                        @foreach (($search_items['closing_date_list'] ?? []) as $key => $item)
                            <option
                                @if ($key == old('closing_date', $search_condition_input_data['closing_date'] ?? null))
                                    selected
                                @endif
                                value="{{ $key }}">
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 vertical-center pl-0">
                    日締め
                </div>
                @error('charge_date')
                <div class="invalid-feedback ml-3">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
@show
