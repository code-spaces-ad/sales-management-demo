{{-- 分類２リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('classification2_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>分類２</b>
            @if(isset($required_classification1))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-3">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="classification2_id" onchange=""
                        id="mySelect2"
                        class="custom-select input-classification2-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('classification2_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['classifications2'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('classification2_id', $target_record_data['classification2_id'] ?? null))
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
            @error('classification2_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
