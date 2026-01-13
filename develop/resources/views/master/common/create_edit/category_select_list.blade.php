{{-- カテゴリーリストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('category_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>カテゴリー</b>
            @if(isset($required_category))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-3">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="category_id" onchange="changeCategory();"
                        id="mySelect2"
                        class="custom-select input-category-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('category_id')) is-invalid @endif">
                    @if(!isset($required_category))
                        <option value="">-----</option>
                    @endif
                    @foreach (($input_items['categories'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('category_id', $target_record_data['category_id'] ?? null))
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
