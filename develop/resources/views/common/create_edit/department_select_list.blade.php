{{-- 部門リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('edit_department_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>部門</b>
            @if(isset($required_department))
                <span class="badge badge-danger align-middle">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-5">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 部門コード --}}
                <input type="number"
                       class="form-control input-department-code h-75 col-5 col-md-4 mr-md-1 d-none"
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
                    @foreach (($input_items['departments'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('department_id', $target_record_data['department_id'] ?? null))
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
