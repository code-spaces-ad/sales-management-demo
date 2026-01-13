{{-- 納品先名Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3">
        <b>納品先名</b>
    </label>
    <div class="d-md-inline-flex col-md-10 pr-md-0">
        <input type="text" name="recipient_name" value="{{ $search_condition_input_data['recipient_name'] ?? '' }}" 
            class="{{ $errors->has('recipient_name') ? 'form-control input-recipient-name is-invalid' : 'form-control input-recipient-name ' }}">
        @error('recipient_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
