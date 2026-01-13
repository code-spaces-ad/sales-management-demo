{{-- カテゴリーリストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('category_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>カテゴリー</b>
        </label>
        <div class="flex-md-column col-md-9 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- カテゴリー名 --}}
                <select name="category_id" onchange="changeCategory();"
                        class="custom-select input-category-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                            @if ($errors->has('category_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($search_items['categories'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('category_id', $search_condition_input_data['category_id'] ?? null))
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
            @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
