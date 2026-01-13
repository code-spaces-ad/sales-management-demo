{{-- 商品区分リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('product_status_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>商品区分</b>
            @if(isset($required_product_status))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-3">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="product_status" onchange=""
                        id="mySelect2"
                        class="custom-select input-product-status-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('product_status')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['product_status'] ?? []) as $key => $val)
                        <option
                            @if ($key == old('product_status', $target_record_data['product_status'] ?? null))
                                selected
                            @endif
                            value="{{ $key }}"
                            data-name="{{ $val }}">
                            {{ $val }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('product_status')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
