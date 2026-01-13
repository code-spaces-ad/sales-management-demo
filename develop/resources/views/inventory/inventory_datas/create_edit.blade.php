{{-- 在庫データ入力画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.inventory.menu.create');
    $next_url = route('inventory.inventory_datas.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    $order_number = '新規';
    $order_title = 'ID';
    if (Route::currentRouteName() === 'inventory.inventory_datas.edit') {
        $headline = config('consts.title.inventory.menu.edit');
        $next_url = route('inventory.inventory_datas.update', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
        $order_number = $target_record_data['id'];
    }
    // コピー用route
    $copy_inventory_store_route = route('inventory.inventory_datas.store');
    // デフォルト伝票日付
    $default_inout_date = \Carbon\Carbon::today()->toDateString();

    /** @see MasterEmployeesConst */
    $maxlength_employee_code = MasterEmployeesConst::CODE_MAX_LENGTH;        // 担当者コード最大桁数
    /** @see MasterProductsConst */
    $maxlength_product_code = MasterProductsConst::CODE_MAX_LENGTH;          // 商品コード最大桁数
        /** @see MasterProductsConst */
    $min_product_code = MasterProductsConst::CODE_MIN_VALUE;   // 商品コード最小値
    /** @see MasterWarehousesConst */
    $maxlength_warehouse_code = MasterWarehousesConst::CODE_MAX_LENGTH;    // 倉庫コード最大桁数
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <form name="editForm" id="editForm" action="{{ $next_url }}" method="POST">
    @method($method)
    @csrf

        <div class="row">
            <div class="col-12 px-0 px-md-4 pb-md-2">
                <div class="card">
                    @if (Route::currentRouteName() === 'inventory.inventory_datas.edit')
                        <div class="card-header form-group d-md-inline-flex m-0">
                            {{-- ID --}}
                            @include('common.create_edit.order_number', ['title' => $order_title])
                            {{-- 更新者/更新日時テーブル --}}
                            @include('common.create_edit.updated_table')
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                            <div class="form-group d-md-inline-flex col-md-6 my-1">
                                <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                    <b>どこから</b>
                                    <span class="badge badge-danger">必須</span>
                                </label>
                                <div class="col-md-9 pr-md-0 pl-0">
                                    {{-- 倉庫コード（セレクトボックス選択用） --}}
                                    <input type="number"
                                        class="form-control input-warehouse-code h-75 col-5 col-md-4 mr-md-1 d-none"
                                        id="from_warehouse_id"
                                        oninput="inputCode(this);"
                                        onchange="changeWarehouseCode(this);"
                                        maxlength="{{ $maxlength_warehouse_code }}"
                                        value="{{ old('from_warehouse_code', $target_record_data['from_warehouse_code'] ?? null) }}">
                                    {{-- 倉庫名 --}}
                                    <select name="from_warehouse_id"
                                            onchange="changeWarehouse(this);"
                                            class="custom-select input-warehouse-select col-9 px-0 mr-md-1 select2_search d-none
                                                @if ($errors->has('from_warehouse_id')) is-invalid @endif">
                                        <option value="">-----</option>
                                        @foreach (($input_items['warehouses'] ?? []) as $item)
                                            <option
                                                @if ($item['id'] == old('from_warehouse_id', $target_record_data['from_warehouse_id'] ?? null))
                                                    selected
                                                @endif
                                                value="{{ $item['id'] }}"
                                                data-name="{{ $item['name'] }}"
                                                data-name-kana="{{ $item['name_kana'] }}"
                                                data-code="{{ $item['code'] }}">
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- to移動先 --}}
                            <div class="form-group d-md-inline-flex col-md-6 my-1">
                                <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                    <b>どこへ</b>
                                    <span class="badge badge-danger">必須</span>
                                </label>
                                <div class="col-md-9 pr-md-0 pl-0">
                                    {{-- 倉庫コード（セレクトボックス選択用） --}}
                                    <input type="number"
                                        class="form-control form-control-sm input-warehouse-code2 w-25 mr-2 d-none"
                                        id="to_warehouse_id"
                                        oninput="inputCode(this);"
                                        onchange="changeWarehouseCode2(this);"
                                        maxlength="{{ $maxlength_warehouse_code }}"
                                        value="{{ old('to_warehouse_code', $target_record_data['to_warehouse_code'] ?? null) }}">
                                    {{-- 倉庫名 --}}
                                    <select name="to_warehouse_id"
                                            onchange="changeWarehouse2(this);"
                                            class="custom-select custom-select-sm input-warehouse-select2 mr-1 select2_search
                                                @if ($errors->has('to_warehouse_id')) is-invalid @endif">
                                        <option value="">-----</option>
                                        @foreach (($input_items['warehouses'] ?? []) as $item)
                                            <option
                                                @if ($item['id'] == old('to_warehouse_id', $target_record_data['to_warehouse_id'] ?? null))
                                                    selected
                                                @endif
                                                value="{{ $item['id'] }}"
                                                data-name="{{ $item['name'] }}"
                                                data-name-kana="{{ $item['name_kana'] }}"
                                                data-code="{{ $item['code'] }}">
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("to_warehouse_id")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 担当者 --}}
                        @include('common.create_edit.employee_select_list')

                        {{-- 入出庫日 --}}
                        <div class="form-group d-md-inline-flex col-md-6 my-1">
                            <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                <b>入出庫日</b>
                                <span class="badge badge-danger">必須</span>
                            </label>
                            <div class="col-md-4 pl-0">

                                <input type="date" name="inout_date" id="inout_date"
                                    value="{{ old('inout_date', $target_record_data['inout_date'] ?? $default_inout_date) }}"
                                    class="form-control input-inout-date{{ $errors->has('inout_date') ? ' is-invalid' : '' }}" >

                                @error('inout_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="from_warehouse_code" value="" class="input-warehouse-code">
        <input type="hidden" name="to_warehouse_code" value="" class="input-warehouse-code2">

        <div class="col-md-12">
            {{-- 入出庫テーブル --}}
            <div class="table-responsive table-fixed">
                @error('detail')
                <div class="invalid-feedback" style="font-weight: bold; font-size: 1.3em;">
                    <i class="fas fa-exclamation-circle"></i>{{ $message }}
                </div>
                @enderror
                <table class="table table-bordered table-responsive-org mb-1" id="inventory_data_table">
                    <thead class="thead-light text-center">
                    <tr>
                        <th scope="col" class="col-md-6">商品</th>
                        <th scope="col" class="col-md-1">数量</th>
                        <th scope="col" class="col-md-4">備考</th>
                        <th scope="col"></th>
                        <th scope="col" class="col-md-1">現在庫</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $inventory_details = collect();
                        if (!empty($target_record_data['inventoryDataDetail'])) {
                            $inventory_details = collect(old('detail', $target_record_data->inventoryDataDetail()->get() ?? []));
                        }
                        $target_detail_count = config('consts.default.orders_received.product_row_count');   // 商品行の枠数
                        $actual_detail_count = count($inventory_details);

                        if ($actual_detail_count < $target_detail_count) {
                            $shortage_count = $target_detail_count - $actual_detail_count;

                            for ($i = 0; $i < $shortage_count; $i++) {
                                $inventory_details->push([]);
                            }
                        }
                        if ($target_detail_count < 1) {
                            $inventory_details->push([],[],[],[],[]);
                        }
                    @endphp

                    {{-- 明細行 --}}
                    @foreach ($inventory_details as $key => $detail)
                        <tr>
                            <th scope="row" class="text-center align-middle pc-no-display">
                                {{ $key + 1 }}
                            </th>
                            {{-- 商品名 --}}
                            <td data-title="商品">
                                <div class="d-block w-100 d-md-flex">
                                    <div class="col-md-3 w-100 mb-1 mb-md-0 p-0 pr-1">
                                        {{-- 商品コード（セレクトボックス選択用） --}}
                                        <input type="number"
                                            class="form-control form-control-sm input-product-code w-25 mr-1 d-none"
                                            id="product_code-{{ $key }}" oninput="inputCode(this);"
                                            onchange="changeProductCodeCreateEdit(this);"
                                            maxlength="{{ $maxlength_product_code }}" min="{{ $min_product_code }}"
                                            placeholder="商品コード">
                                        <select name="detail[{{ $key }}][product_id]"
                                                onchange="changeProductCreateEdit(this);"
                                                id="product_id-{{ $key }}"
                                                class="custom-select custom-select-sm input-product-select select2_search_product
                                                @if($errors->has("detail.{$key}.product_id")) is-invalid @endif ">
                                            <option value="" data-code="-----">-----</option>
                                            @foreach (($input_items['products'] ?? []) as $item)
                                                <option
                                                    @if ($item['id'] == ($detail['product_id'] ?? null))
                                                        selected
                                                    @endif
                                                    value="{{ $item['id'] }}"
                                                    data-id="{{ $item['id'] }}"
                                                    data-code="{{ $item['code'] }}"
                                                    data-name="{{ $item['name'] }}"
                                                    data-name-kana="{{ $item['name_kana'] }}"
                                                    data-unit-name="{{ $item['product_unit_name'] }}"
                                                    data-tax-rate="{{ $item['consumption_tax_rate'] }}"
                                                    data-amount-rounding-method-id="{{ $item['amount_rounding_method_id'] }}">
                                                    {{ $item['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("detail.{$key}.product_id")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-9 mb-0 px-0 product-group">
                                        <input type="text" name="detail[{{ $key }}][product_name]"
                                            value="{{ old('detail.' . $key . '.product_name', $detail['product_name'] ?? '') }}"
                                            class="form-control form-control-sm input-product-name mr-1{{ $errors->has('detail.' . $key . '.product_name') ? ' is-invalid' : '' }}"
                                            placeholder="商品名">

                                        @error("detail.{$key}.product_name")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </td>
                            {{-- 数量 --}}
                            <td class="text-center align-middle" data-title="数量">

                                <input type="text" name="detail[{{ $key }}][quantity]"
                                    value="{{ old('detail.' . $key . '.quantity', $detail->quantity_digit_cut ?? '') }}"
                                    class="form-control form-control-sm input-quantity text-right mr-1{{ $errors->has('detail.' . $key . '.quantity') ? ' is-invalid' : '' }}"
                                    inputmode="numeric">

                                @error("detail.{$key}.quantity")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>

                            {{-- 備考 --}}
                            <td class="text-center align-middle" data-title="備考">

                                <input type="text" name="detail[{{ $key }}][note]"
                                    value="{{ old('detail.' . $key . '.note', $detail['note'] ?? '') }}"
                                    class="form-control form-control-sm input-note mr-1{{ $errors->has('detail.' . $key . '.note') ? ' is-invalid' : '' }}" >

                                @error("detail.{$key}.note")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            {{-- クリアボタン --}}
                            <td class="text-center align-middle" data-title="クリア">
                                <div class="text-center w-100">
                                    <button type="button"
                                            class="btn btn-secondary btn-xs button-product-clear m-0"
                                            onclick="clearProduct(this);">
                                    </button>
                                </div>
                            </td>
                            {{-- 現在庫 --}}
                            <td class="text-center align-middle" data-title="現在庫">
                                <select id="inventory-stock-select-{{ $key }}"
                                        class="form-control form-control-sm mr-1 text-right inventory-stock-select"
                                        style="appearance: none;"
                                        disabled>
                                    <option>0</option>
                                    @foreach(($input_items['inventory_stock_data']) as $item)
                                        <option
                                            value="{{ $item['id'] }}"
                                            data-warehouse_id="{{ $item['warehouse_id'] }}"
                                            data-product_id="{{ $item['product_id'] }}">
                                            {{ number_format($item['inventory_stocks']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </tbody>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="buttons-area text-center mt-2">
            <div class="row col-md-12 p-0 m-0">
                <div class="row col-md-6 flex-row-reverse mx-auto d-block text-center text-md-right pr-md-1">
                    <a id="return" class="btn btn-primary back_active"
                    href="{{ session($session_inventory_key, route('inventory.inventory_datas.index')) }}">
                        一覧画面へ戻る
                    </a>
                </div>
                <div class="row col-md-6 flex-row-reverse mx-auto d-block mt-2 mt-md-0 text-center text-md-left pl-md-2">
                    @if (config('consts.default.common.use_register_clear_button'))
                        <a class="btn btn-secondary" onclick="clearInput();">クリア</a>
                    @endif

                    @if (!$is_edit_route)
                        {{-- 登録ボタン、更新ボタン --}}
                        <button type="submit" id="btn_submit" value="{{ $next_btn_text }}" class="btn btn-primary" style="display:none;"></button>
                        <button type="button" id="store" class="btn btn-primary"
                                data-toggle="modal" data-target="#confirm-store">
                            <i class="far fa-edit"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                style="display: none;"></div>
                            {{ $next_btn_text }}
                        </button>
                    @endif

                    {{-- 複製ボタン、削除ボタンは、編集画面のみ表示 --}}
                    @if ($is_edit_route)
                        {{-- 複製ボタン --}}
                        <button id="copy" type="button" class="btn btn-outline-primary mr-2" data-toggle="modal"
                                data-target="#confirm-copy">
                            <i class="fas fa-copy"></i>
                            <div class="spinner-border spinner-border-sm text-primary align-middle" role="status"
                                style="display: none;"></div>
                            複製
                        </button>
                        {{-- 削除ボタン --}}
                        <button id="delete" type="button" class="btn btn-danger d-none" data-toggle="modal"
                                data-target="#confirm-delete">
                            <i class="fas fa-times"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                style="display: none;"></div>
                            削除
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- モーダル変更対象行 保存用 --}}
        <input type="hidden" id="modal_target_row" name="modal_target_row" value="">

    </form>

    <div>
        {{-- エラー情報 --}}
        <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">
        <input type="hidden" name="copy_number" id="copy_number" value="0">
        <input type="hidden" id="default_inout_date" name="default_inout_date" value="{{ $default_inout_date }}">
    </div>

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm" action="{{ route('inventory.inventory_datas.destroy', $target_record_data['id']) }}" method="POST">
        @method('DELETE')
        @csrf
        </form>

        <form name="copyForm" id="copyForm" action="{{ route('inventory.inventory.copy_order') }}">
        @method('POST')
        @csrf
            {{-- 伝票ID用 --}}
            <input type="hidden" id="select_inventory_data_id" name="select_inventory_data_id"
                value="{{ $target_record_data['id'] }}">
        </form>
    @endif

    {{-- Confirm Store Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-store')
        @if($is_edit_route)
            @slot('confirm_message', config('consts.message.common.confirm.update') )
        @else
            @slot('confirm_message', config('consts.message.common.confirm.store') )
        @endif
        @slot('onclick_btn_ok', "store();return false;")
    @endcomponent

    {{-- Confirm Delete Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-delete')
        @slot('confirm_message', config('consts.message.common.confirm.delete') )
        @slot('onclick_btn_ok', "destory();return false;")
    @endcomponent

    {{-- Confirm Copy Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-copy')
        @slot('confirm_message', config('consts.message.common.confirm.copy') )
        @slot('onclick_btn_ok', "copy('". $copy_inventory_store_route ."');return false;")
    @endcomponent

    {{-- Search Product Modal --}}
    @component('components.search_product_modal')
        @slot('modal_id', 'search-product')
        @slot('products', $input_items['products'])
        @slot('onclick_select_product', "selectProductSearchProductModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/inventory/inventory_datas/create_edit.js') }}"></script>

@endsection
