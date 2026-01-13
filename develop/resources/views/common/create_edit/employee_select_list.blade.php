{{-- 担当者リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('employee_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-3 col-form-label pl-0 pb-md-3">
            <b>担当者</b>
            <span class="badge badge-danger align-middle">必須</span>
        </label>
        <div class="flex-md-column col-md-9 pr-md-0 pl-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 担当者コード --}}
                <input type="number"
                       class="form-control input-employee-code h-75 col-5 col-md-4 mr-md-1 d-none"
                       id="employee_code" oninput="inputCode(this);"
                       onchange="changeEmployeeCode(this);"
                       maxlength="{{ $maxlength_employee_code }}">
                {{-- 担当者 --}}
                <select name="employee_id" onchange="changeEmployee();"
                        class="custom-select input-employee-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('employee_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['employees'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('employee_id', $target_record_data['employee_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}"
                            data-code="{{ $item['code'] }}">
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
