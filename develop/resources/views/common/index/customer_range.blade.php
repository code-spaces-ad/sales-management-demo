{{-- 得意先Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('customer_range')
    <div class="form-group d-md-inline-flex col-md-12 my-1">
        <label class="col-md-1 col-form-label pl-0 pb-md-2">
            <b>得意先</b>
        </label>
        <div class="flex-md-column col-md-12 pr-md-0">
            <div class="d-md-inline-flex col-md-4 pr-md-0 pl-0">
                {{-- 得意先コード --}}
                <input type="number"
                       class="form-control h-75 input-customer-start-code col-5 col-md-4 mr-md-1 clear-value"
                       id="customer_id_start_code" oninput="inputCode(this);"
                       onchange="changeStartCustomerCode(this);"
                       placeholder="得意先コード">
                {{-- 得意先 --}}
                <select name="customer_id[start]" onchange="changeStartCustomer();"
                        class="custom-select input-customer-start-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                        @if($errors->has('customer_id.start')) is-invalid @endif">
                    @if(!isset($required_customer))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['customers'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('customer_id.start', $search_condition_input_data['customer_id']['start'] ?? null))
                            selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}"
                            data-employee-id="{{ $item['employee_id'] ?? '' }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="d-inline-flex align-items-center mx-2">～</span>
            <div class="d-md-inline-flex col-md-4 pr-md-0 pl-0">
                {{-- 得意先コード --}}
                <input type="number"
                       class="form-control h-75 input-customer-end-code col-6 col-md-4 mr-md-1 clear-value"
                       id="customer_id_end_code" oninput="inputCode(this);"
                       onchange="changeEndCustomerCode(this);"
                       placeholder="得意先コード">
                {{-- 得意先 --}}
                <select name="customer_id[end]" onchange="changeEndCustomer();"
                        class="custom-select input-customer-end-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                        @if($errors->has('customer_id.end')) is-invalid @endif">
                    @if(!isset($required_customer))
                        <option value="">-----</option>
                    @endif
                    @foreach (($search_items['customers'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('customer_id.end', $search_condition_input_data['customer_id']['end'] ?? null))
                            selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}"
                            data-employee-id="{{ $item['employee_id'] ?? '' }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('customer_id.*')
            <div class="invalid-feedback ml-3">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
