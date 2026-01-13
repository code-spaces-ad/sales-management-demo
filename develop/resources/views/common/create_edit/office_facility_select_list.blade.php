{{-- 事業所リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('edit_office_facility_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>事業所</b>
            @if(isset($required_office_facility))
                <span class="badge badge-danger align-middle">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-5">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 事業所コード --}}
                <input type="number"
                       class="form-control input-office-facility-code h-75 col-5 col-md-4 mr-md-1 d-none clear-value"
                       id="office_facilities_id_code" oninput="inputCode(this);"
                       onchange="changeOfficeFacilityCode(this);"
                       placeholder="事業所コード">
                {{-- 事業所 --}}
                <select name="office_facilities_id" onchange="changeOfficeFacility();"
                        class="office-facility-select input-office-facility-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                        @if($errors->has('office_facilities_id')) is-invalid @endif">
                    @if(!isset($required_office_facility))
                        <option value="">-----</option>
                    @endif
                    @foreach (($input_items['office_facilities'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('office_facilities_id', $target_record_data['office_facilities_id'] ?? null))
                            selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}"
                            data-department-id="{{ $item['department_id'] }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('office_facilities_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
