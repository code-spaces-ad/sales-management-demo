{{-- 伝票番号Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('order_number')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-2 pl-0 pb-md-3">
            <b>伝票番号</b>
        </label>
        <div class="flex-md-column col-md-4 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">                
                <input type="text" name="order_number" id="order_number" 
                    class="form-control form-control-sm input-note clear-value {{ $errors->has('order_number') ? 'is-invalid' : '' }}" 
                    value="{{ old('order_number', $search_condition_input_data['order_number'] ?? '') }}" />
            </div>
            @error('order_number')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
