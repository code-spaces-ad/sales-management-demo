{{-- 事業所リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('office_facility_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>事業所</b>
        </label>
        <div class="flex-md-column col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 事業所コード --}}
                <input type="number"
                       class="form-control h-75 input-office-facility-code col-5 col-md-3 mr-md-1 clear-value"
                       id="office_facility_id_code" oninput="inputCode(this);"
                       onchange="changeOfficeFacilityCode(this);"
                       placeholder="事業所コード">
                {{-- 事業所 --}}
                <select name="office_facility_id" onchange="changeOfficeFacility();"
                        class="office-facility-select input-office-facility-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                        @if($errors->has('office_facility_id')) is-invalid @endif">
                    @if(!isset($required_office_facility))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['office_facilities'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('office_facility_id', $search_condition_input_data['office_facility_id'] ?? null))
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
            @error('office_facility_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
