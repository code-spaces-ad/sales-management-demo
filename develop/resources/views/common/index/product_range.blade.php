{{-- 商品Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('product_range')
    <div class="form-group d-md-inline-flex col-md-12 my-1">
        <label class="col-md-1 col-form-label pl-0 pb-md-2">
            <b>商品</b>
        </label>
        <div class="flex-md-column col-md-12 pr-md-0">
            <div class="d-md-inline-flex col-md-4 pr-md-0 pl-0">
                {{-- 商品コード（セレクトボックス選択用） --}}
                <input type="number"
                       class="form-control h-75 input-product-start-code col-5 col-md-4 mr-md-1 clear-value"
                       id="product_start_code"
                       oninput="inputCode(this);"
                       onchange="changeStartProductCode(this);"
                       placeholder="商品コード">
                {{-- 商品名 --}}
                <select name="product_id[start]" onchange="changeStartProduct();"
                        class="custom-select input-product-start-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                            @if ($errors->has('product_id.start')) is-invalid @endif">
                    @if(!isset($required_product))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['products'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('product_id.start', $search_condition_input_data['product_id']['start'] ?? null))
                            selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="d-inline-flex align-items-center mx-2">～</span>
            <div class="d-md-inline-flex col-md-4 pr-md-0 pl-0">
                {{-- 商品コード（セレクトボックス選択用） --}}
                <input type="number"
                       class="form-control h-75 input-product-end-code col-5 col-md-4 mr-md-1 clear-value"
                       id="product_end_code"
                       oninput="inputCode(this);"
                       onchange="changeEndProductCode(this);"
                       placeholder="商品コード">
                {{-- 商品名 --}}
                <select name="product_id[end]" onchange="changeEndProduct();"
                        class="custom-select input-product-end-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                            @if ($errors->has('product_id.end')) is-invalid @endif">
                    @if(!isset($required_product))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['products'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('product_id.end', $search_condition_input_data['product_id']['end'] ?? null))
                            selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('product_id.*')
            <div class="invalid-feedback ml-3">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
