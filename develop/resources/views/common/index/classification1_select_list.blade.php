{{-- 分類１リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('classification1_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>分類１</b>
        </label>
        <div class="flex-md-column col-md-9 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 種別 --}}
                <select name="classification1_id"
                        class="custom-select input-classification1-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                            @if ($errors->has('classification1_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($search_items['classifications1'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('classification1_id', $search_condition_input_data['classification1_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}">
                            {{ StringHelper::getNameWithId($item['code_zerofill'], $item['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('classification1_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
