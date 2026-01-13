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
        <div class="flex-md-column col-sm-2">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- カテゴリー名 --}}
                <select name="category_id"
                        class="custom-select input-category-name-select
                        @if($errors->has('category_id')) is-invalid @endif">
                    @if(!isset($required_category))
                        <option value="">-----</option>
                    @endif
                    @foreach (($input_items['categories'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('category_id', $target_record_data['category_id']))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-name="{{ $item['name'] }}">
                            {{ StringHelper::getNameWithId($item['code'], $item['name']) }}
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
