{{-- 得意先リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('customer_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>得意先</b>
        </label>
        <div class="flex-md-column col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 得意先コード --}}
                <input type="number"
                       class="form-control h-75 input-customer-code col-5 col-md-3 mr-md-1 clear-value"
                       id="customer_id_code" oninput="inputCode(this);"
                       onchange="changeCustomerCode(this);"
                       placeholder="得意先コード">
                {{-- 得意先 --}}
                <select name="customer_id" onchange="changeCustomer();"
                        class="custom-select input-customer-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                        @if($errors->has('customer_id')) is-invalid @endif">
                    @if(!isset($required_customer))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['customers'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('customer_id', $search_condition_input_data['customer_id'] ?? null))
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
            @error('customer_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
