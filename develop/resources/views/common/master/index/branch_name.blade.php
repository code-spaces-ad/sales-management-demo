    {{-- 支所名Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3">
        <b>支所名</b>
    </label>
    <div class="d-md-inline-flex col-md-10 pr-md-0">
        <input type="text" name="branch_name" value="{{ $search_condition_input_data['branch_name'] ?? '' }}"
            class="form-control input-branch-name{{ $errors->has('branch_name') ? ' is-invalid' : '' }}">
        @error('branch_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
