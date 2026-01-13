{{-- 相手先商品番号Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-6 my-1">
    <label class="col-md-2 col-form-label pl-0 pb-md-3" style="font-size: 0.8rem">
        <b>相手先商品番号</b>
    </label>
    <div class="d-md-inline-flex col-md-9 pr-md-0">
        <input type="text" name="customer_product_code" value="{{ $search_condition_input_data['customer_product_code'] ?? '' }}"
            class="{{ $errors->has('customer_product_code') ? 'form-control input-customer-product-code is-invalid' : 'form-control input-name ' }}">
        @error('customer_product_code')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
