{{-- 部門リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('department_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>部門</b>
        </label>
        <div class="flex-md-column col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 部門コード --}}
                <input type="number"
                       class="form-control h-75 input-department-code col-5 col-md-3 mr-md-1 clear-value"
                       id="department_id_code" oninput="inputCode(this);"
                       onchange="changeDepartmentCode(this);"
                       placeholder="部門コード">
                {{-- 部門 --}}
                <select name="department_id" onchange="changeDepartment();"
                        class="custom-select input-department-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                        @if($errors->has('department_id')) is-invalid @endif">
                    @if(!isset($required_department))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['departments'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('department_id', $search_condition_input_data['department_id'] ?? null))
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
            @error('department_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
