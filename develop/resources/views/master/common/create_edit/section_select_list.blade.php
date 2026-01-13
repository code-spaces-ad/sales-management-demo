{{-- 種別リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('section_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>管理部署</b>
            @if(isset($required_section))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-3">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="section_id" onchange=""
                        id="mySelect2"
                        class="custom-select input-section-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('section_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['sections'] ?? []) as $item)
                        <option
                                @if ($item['id'] == old('section_id', $target_record_data['section_id'] ?? null))
                                    selected
                                @endif
                                value="{{ $item['id'] }}"
                                data-name="{{ $item['name'] }}"
                                data-code="{{ $item['code'] }}"
                                data-name-kana="{{ $item['name_kana'] }}">
                            {{ StringHelper::getNameWithId($item['code_zerofill'], $item['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('section_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
