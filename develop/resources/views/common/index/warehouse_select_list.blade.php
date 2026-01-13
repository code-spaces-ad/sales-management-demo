{{-- 倉庫リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('warehouse_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-2 pl-0 pb-md-3">
            <b>倉庫</b>
        </label>
        <div class="flex-md-column col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                {{--倉庫コード（セレクトボックス選択用） --}}
                <input type="number"
                       class="form-control input-warehouse-code col-4 col-md-3 mr-md-1 d-none clear-value"
                       id="warehouse_code"
                       oninput="inputCode(this);" onchange="changeWarehouseCode(this);"
                       maxlength="{{ MasterWarehousesConst::CODE_MAX_LENGTH }}"
                       min="{{ MasterwarehousesConst::CODE_MIN_VALUE }}">
                {{--倉庫名 --}}
                <select name="warehouse_id" onchange="changeWarehouse();"
                        class="custom-select input-warehouse-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                            @if ($errors->has('warehouse_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($search_items['warehouses'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('warehouse_id', $search_condition_input_data['warehouse_id'] ?? null))
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
            @error('warehouse_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
