{{-- 種別リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('kind_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>種別</b>
            @if(isset($required_kind))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-3">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="kind_id" onchange=""
                        id="mySelect2"
                        class="custom-select input-kind-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('kind_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['kinds'] ?? []) as $item)
                        <option
                                @if ($item['id'] == old('kind_id', $target_record_data['kind_id'] ?? null))
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
            @error('kind_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
