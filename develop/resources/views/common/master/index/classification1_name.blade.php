{{-- 分類1名Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3">
        <b>分類1名</b>
    </label>
    <div class="d-md-inline-flex col-md-9 pr-md-0">
        <input type="text" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}"
            class="form-control input-name{{ $errors->has('name') ? ' is-invalid' : '' }}">
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
