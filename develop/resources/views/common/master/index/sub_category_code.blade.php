{{-- サブカテゴリコードBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3">
        <b>サブカテゴリコード</b>
    </label>
    <div class="col-sm-9 row">
        <div class="col-sm-5 input-tilde">
            <input type="number" name="sub_category_code[start]" id="sub_category_code_start" value="{{ old('sub_category_code.start', $search_condition_input_data['sub_category_code']['start'] ?? '') }}"
                class="form-control input-sub-category-code-start{{ $errors->has('sub_category_code.start') ? ' is-invalid' : '' }}" min="1">
        </div>
        <div class="col-sm-5">
            <input type="number" name="sub_category_code[end]" id="sub_category_code_end" value="{{ old('sub_category_code.end', $search_condition_input_data['sub_category_code']['end'] ?? '') }}"
                class="form-control input-sub-category-code-end{{ $errors->has('sub_category_code.end') ? ' is-invalid' : '' }}" min="1">
        </div>
        @error('sub_category_code.*')
        <div class="invalid-feedback ml-3">{{ $message }}</div>
        @enderror
    </div>
</div>
