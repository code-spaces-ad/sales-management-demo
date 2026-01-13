{{-- 仕入先リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('supplier_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-3 col-form-label pl-0 pb-md-3">
            <b>仕入先</b>
            <span class="badge badge-danger">必須</span>
        </label>
        <div class="flex-md-column col-md-9 pr-md-0 pl-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{-- 仕入先コード --}}
                <input type="number"
                       class="form-control h-75 input-supplier-code col-5 col-md-4 mr-md-1"
                       id="supplier_code" oninput="inputCode(this);"
                       onchange="changeSupplierCodeCreateEdit(this);"
                       maxlength="{{ $maxlength_supplier_code }}"
                       value="{{ $target_record_data->supplier_code_zero_fill }}"
                       placeholder="仕入先コード">
                {{-- 仕入先 --}}
                <select name="supplier_id" onchange="changeSupplierCreateEdit();"
                        id="mySelect2"
                        class="custom-select input-supplier-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('supplier_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['suppliers'] ?? []) as $item)
                        {{--// 仕入は3（明細単位）/ 3(四捨五入)固定 --}}
                        <option
                            @if ($item['id'] == old('supplier_id', $target_record_data['supplier_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}"
                            data-tax-calc-type="{{ $item['tax_calc_type_id'] }}"
                            data-tax-rounding-method="{{ $item['tax_rounding_method_id'] }}">
                            {{ $item['name'] }} ({{ \App\Enums\TaxCalcType::asSelectArray()[$item['tax_calc_type_id']] }} - {{ \App\Enums\RoundingMethodType::asSelectArray()[$item['tax_rounding_method_id']] }})
                        </option>
                    @endforeach
                </select>
            </div>
            @error('supplier_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
