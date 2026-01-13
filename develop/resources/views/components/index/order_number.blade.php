{{-- 伝票番号Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-form-label col-md-2 pl-0 pb-md-3">
        <b>{{ $title }}</b>
    </label>
    <div class="col-md-4">
        <input type="text" name="{{ $order_number }}" id="{{ $order_number }}" value="{{ old($order_number, $search_condition_input_data[$order_number] ?? null) }}" 
            class="form-control input-order-number clear-value{{ $errors->has($order_number) ? ' is-invalid' : '' }}">

        @error($order_number)
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
