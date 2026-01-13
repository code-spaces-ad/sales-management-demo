{{-- カテゴリコードBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3">
        <b>カテゴリコード</b>
    </label>
    <div class="col-sm-9 row">
        <div class="col-sm-5 input-tilde">
            <input type="number" name="category_code[start]" id="category_code_start" value="{{ old('category_code.start', $search_condition_input_data['category_code']['start'] ?? '') }}"
                   class="form-control input-category-code-start{{ $errors->has('category_code.start') ? ' is-invalid' : '' }}" min="1">
        </div>
        <div class="col-sm-5">
            <input type="number" name="category_code[end]" id="category_code_end" value="{{ old('category_code.end', $search_condition_input_data['category_code']['end'] ?? '') }}"
                   class="form-control input-category-code-end{{ $errors->has('category_code.end') ? ' is-invalid' : '' }}" min="1">
        </div>
        @error('category_code.*')
        <div class="invalid-feedback ml-3">{{ $message }}</div>
        @enderror
    </div>
</div>
