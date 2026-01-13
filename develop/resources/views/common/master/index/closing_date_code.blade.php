{{-- 締日コードBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3">
        <b>コード</b>
    </label>
    <div class="col-sm-6 row">
        <div class="col-sm-5 input-tilde">
            <input type="number" name="code[start]" id="code_start" value="{{ old('code.start', $search_condition_input_data['code']['start'] ?? '') }}"
                class="form-control input-code-start{{ $errors->has('code.start') ? ' is-invalid' : '' }}" min="1">
        </div>
        <div class="col-sm-5">
            <input type="number" name="code[end]" id="code_end" value="{{ old('code.end', $search_condition_input_data['code']['end'] ?? '') }}"
                class="form-control input-code-end{{ $errors->has('code.end') ? ' is-invalid' : '' }}" min="1">
        </div>
        @error('code.*')
        <div class="invalid-feedback ml-3">{{ $message }}</div>
        @enderror
    </div>
</div>
