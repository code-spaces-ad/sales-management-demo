{{-- 仕入先リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('supplier_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>仕入先</b>
            @if(isset($required_section))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-6">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="supplier_id" onchange="changeSupplierCreateEdit();"
                        id="mySelect2"
                        class="custom-select input-supplier-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('supplier_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['suppliers'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('supplier_id', $target_record_data['supplier_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}"
                            data-tax-rounding-method="{{ \App\Enums\RoundingMethodType::ROUND_OFF }}">
                            {{ StringHelper::getNameWithId($item['code_zerofill'], $item['name']) }}
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
