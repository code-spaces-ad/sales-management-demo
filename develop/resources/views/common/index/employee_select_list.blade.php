{{-- 担当者リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('employee_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>担当者</b>
        </label>
        <div class="flex-md-column col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="employee_id" onchange="changeEmployee()"
                        class="custom-select input-employee-select mr-1 select2_search d-none clear-select
                            @if ($errors->has('employee_id')) is-invalid @endif">
                    @if (!isset($required_employee))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['employees'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('employee_id', $search_condition_input_data['employee_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('employee_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
