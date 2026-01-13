{{-- 商品リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('product_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>商品</b>
        </label>
        <div class="flex-md-column col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 商品コード（セレクトボックス選択用） --}}
                <input type="number"
                       class="form-control h-75 input-product-code col-5 col-md-3 mr-md-1 clear-value"
                       id="product_code"
                       oninput="inputCode(this);"
                       onchange="changeProductCode(this);"
                       placeholder="商品コード">
                {{-- 商品名 --}}
                <select name="product_id" onchange="changeProduct();"
                        class="custom-select input-product-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                            @if ($errors->has('product_id')) is-invalid @endif">
                    @if(!isset($required_product))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['products'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('product_id', $search_condition_input_data['product_id'] ?? null))
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
            @error('product_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
