{{-- 商品名Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3">
        <b>商品名</b>
    </label>
    <div class="d-md-inline-flex col-md-9 pr-md-0">
        <input type="text" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}" 
            class="{{ $errors->has('name') ? 'form-control input-name is-invalid' : 'form-control input-name ' }}">
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
