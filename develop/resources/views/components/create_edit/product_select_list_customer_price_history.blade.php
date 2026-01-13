{{-- 商品リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="d-block w-100 d-md-flex">
    <div class="col-md-3 w-100 mb-1 mb-md-0 p-0 pr-1">
        {{-- 商品コード（セレクトボックス選択用） --}}
        <input type="number"
               class="form-control form-control-sm input-product-code clear-value d-none"
               oninput="inputCode(this);"
               onchange="changeProductCodeCreateEdit(this);"
               maxlength="{{ MasterProductsConst::CODE_MAX_LENGTH }}"
               placeholder="商品コード">
        <select name="detail[{{ $key }}][product_id]"
                onchange="changeProductCreateEdit(this);"
                class="custom-select custom-select-sm input-product-select clear-select select2_search_product
                    @if ($errors->has("detail.{$key}.product_id")) is-invalid @endif d-none">
            <option value="" data-code="-----">-----</option>
            @foreach (($input_items['products'] ?? []) as $item)
                <option
                    @if ($item['id'] == ($detail['product_id'] ?? null))
                        selected
                    @endif
                    value="{{ $item['id'] }}"
                    data-code="{{ $item['code'] }}"
                    data-name="{{ $item['name'] }}"
                    data-name-kana="{{ $item['name_kana'] }}"
                    data-unit-name="{{ $item['product_unit_name'] }}"
                    data-unit-price="{{ $item['unit_price_floor'] }}"
                    data-purchase-unit-price="{{ $item['purchase_unit_price'] }}"
                    data-tax-type-id="{{ $item['tax_type_id'] }}"
                    data-reduced-tax-flag="{{ $item['reduced_tax_flag'] }}"
                    data-unit-price-decimal-digit="{{ $item['unit_price_decimal_digit'] ?? 0 }}"
                    data-quantity-decimal-digit="{{ $item['quantity_decimal_digit'] ?? 0 }}"
                    data-amount-rounding-method-id="{{ $item['amount_rounding_method_id'] }}"
                    data-quantity-rounding-method-id="{{ $item['quantity_rounding_method_id'] }}">
                    {{ $item['name'] }}
                </option>
            @endforeach
        </select>
        @error("detail.{$key}.product_id")
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    {{-- 商品名 --}}
    <div class="col-md-9 mb-0 pl-0 pr-0 product-group">

        <input type="text" name="detail[{{$key}}][product_name]"
               value="{{ old("detail.{$key}.product_name", $detail['product_name'] ?? null) }}"
               onfocus="getCustomerUnitPriceHistory(this);"
               class="form-control form-control-sm input-product-name clear-value mr-1 {{ ($errors->has("detail.{$key}.product_name") ? ' is-invalid' : '') }}"
               placeholder="商品名"
               readonly >

        @error("detail.{$key}.product_name")
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
