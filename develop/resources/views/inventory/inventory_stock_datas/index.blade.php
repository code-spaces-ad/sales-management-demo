{{-- 在庫調整画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.inventory.menu.stock_datas.index');
    $next_url = route('inventory.inventory_stock_datas.index');
    $excel_download_url = route('inventory.inventory_stock_datas.download_excel');
    $method = 'GET';

    /** @see MasterProductsConst */
    $maxlength_product_code = MasterProductsConst::CODE_MAX_LENGTH;   // 商品コード最大桁数
    $min_product_code = MasterProductsConst::CODE_MIN_VALUE;   // 商品コード最小値

     /** @see MasterWarehoussConst */
    $maxlength_warehouse_code = MasterWarehousesConst::CODE_MAX_LENGTH;   // 商品コード最大桁数
    $min_warehouse_code = MasterwarehousesConst::CODE_MIN_VALUE;   // 商品コード最小値
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">

        <form name="searchForm" action="{{ $next_url }}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">
            <input type="hidden" name="post_url" value="{{ $post_url }}">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- 倉庫 --}}
                    @include('common.index.warehouse_select_list')

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- 商品 --}}
                        @include('common.index.product_select_list')

                        {{-- カテゴリー --}}
                        @include('common.index.category_select_list')
                    </div>

                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        {{-- 並び順 --}}
                        <label class="col-md-2 col-form-label pl-0 pb-md-3">
                            <b>並び順</b>
                        </label>
                        <div class="d-md-inline-flex col-md-10 pr-md-0">
                            <select name="sort"
                                    class="custom-select input-sort-select clear-select
                                    @if($errors->has('sort')) is-invalid @endif">
                                @foreach (($search_items['sort_types'] ?? []) as $key => $value)
                                    <option
                                        @if ($key == ($search_condition_input_data['sort'] ?? null))
                                            selected
                                        @endif
                                        value="{{ $key }}">
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sort')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>

            {{-- 在庫調整トグルスイッチ --}}
            <div class="custom-control custom-switch form-check form-check-input text-right d-none">

                <input type="checkbox" name="adjust_stocks" value="0" id="adjust_stocks_switch"
                    {{ old('adjust_stocks', session($session_adjust_stocks_key, 0)) ? 'checked' : '' }}
                    class="custom-control-input input-adjust-stocks{{ $errors->has('adjust_stocks') ? ' is-invalid' : '' }}"
                    onchange="adjustStocksPost(this)">

                <label class="custom-control-label" for="adjust_stocks_switch">在庫調整</label>
            </div>
        </form>

        <div class="col-md-12 d-flex justify-content-between mb-2">
            <div class="download-area col-6  pl-0">
                <div class="d-inline-flex">
                    <div class="mr-2">

                        <form name="downloadForm" id="download_form" action="{{ $excel_download_url }}" method="POST">
                        @method($method)
                            <input type="hidden" name="product_id" value="{{ $search_condition_input_data['product_id'] ?? '' }}">
                            <input type="hidden" name="warehouse_id" value="{{ $search_condition_input_data['warehouse_id'] ?? '' }}">

                            {{-- Excelダウンロードボタン --}}
                            <button type="submit" class="btn btn-success"
                                @if ($search_result['inventory_stock_datas']->isEmpty()) disabled @endif>
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12" style="margin-top: 20px;">
            {{ $search_result['inventory_stock_datas'] ? $search_result['inventory_stock_datas']->appends($search_condition_input_data)->links() : null }}
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list" id="stocks_data_table">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th style="width: 20%">倉庫名</th>
                        <th style="width: 30%">商品名</th>
                        <th style="width: 20%">在庫数</th>
                        <th style="width: 20%">仕入金額合計</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['inventory_stock_datas'] ?? [] as $key => $warehouse)
                        <tr>
                            <td class="text-left align-middle" data-title="倉庫名">
                                <div style="font-size: 1.2em;">
                                    {{ $warehouse['warehouse_name'] }}
                                </div>
                            </td>

                            <td class="text-left align-middle" data-title="商品名">
                                <a id="edit_link_{{ $key }}" class="product_name"
                                   href="{{ route('inventory.inventory_stock_datas.edit',
                                            [
                                                'product_id' => $warehouse['product_id'],
                                                'warehouse_id' => $warehouse['warehouse_id']
                                            ]
                                   )}}">
                                    {{ $warehouse['product_name'] }}
                                </a>
                            </td>

                            <td class="text-right align-middle" data-title="在庫数">
                                <input type="text" name="detail[{{ $key }}][inventory_stock]" id="stocks_{{ $key }}"
                                    value="{{ old("detail[$key][inventory_stock]", $warehouse['stock']) }}"
                                    class="text-right inventory_stock form-control{{ $errors->has('detail.*.inventory_stock') ? ' is-invalid' : '' }}"
                                    data-warehouse-id="{{ $warehouse['warehouse_id'] }}" data-product-id="{{ $warehouse['product_id'] }}" onchange="adjustInventoryValue(this);">

                                <div class="invalid-feedback display-none">
                                    {{ config('consts.message.common.alert.number') }}
                                </div>
                            </td>

                            <td class="text-right align-bottom" data-title="仕入金額">
                                <label id="total_purchase_{{ $key }}">{{ number_format($warehouse['purchase_total_price'] ?? 0) }}</label>
                                <div class="invalid-feedback display-none">
                                    {{ config('consts.message.common.alert.number') }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['inventory_stock_datas'] ? $search_result['inventory_stock_datas']->appends($search_condition_input_data)->links() : null }}
        </div>
    </div>

    {{-- Search Product Modal --}}
    @component('components.search_product_modal')
        @slot('modal_id', 'search-product')
        @slot('products', $search_items['products'])
        @slot('onclick_select_product', "selectProductSearchProductModal(this);")
    @endcomponent

    {{-- Search warehouse Modal --}}
    @component('components.search_warehouse_modal')
        @slot('modal_id', 'search-warehouse')
        @slot('warehouses', $search_items['warehouses'])
        @slot('onclick_select_warehouse', "selectWarehouseSearchWarehouseModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/inventory.js') }}"></script>
    <script src="{{ mix('js/app/inventory/index.js') }}"></script>
    <script src="{{ mix('js/app/inventory/inventory_stock_datas/index.js') }}"></script>
@endsection
