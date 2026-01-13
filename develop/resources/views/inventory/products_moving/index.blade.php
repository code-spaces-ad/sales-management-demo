{{-- 在庫データ一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    use Carbon\Carbon;

    $headline = config('consts.title.inventory.menu.products_moving');
    $next_url = route('inventory.products_moving.index');
    $excel_download_url = route('inventory.products_moving.download_excel');
    $method = 'GET';
    /** @see MasterProductsConst */
    $maxlength_product_code = MasterProductsConst::CODE_MAX_LENGTH;   // 商品コード最大桁数

    // デフォルトMAX日付・月
    $default_max_date = config('consts.default.common.default_max_date');
    $default_max_month = config('consts.default.common.default_max_month');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <form name="searchForm" action="{{ $next_url }}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- 納品日 --}}
                    @include('common.index.inout_date')

                    {{-- 商品名 --}}
                    @include('common.index.product_select_list', ['required_product' => true])

                    {{-- 倉庫 --}}
                    @include('common.index.warehouse_select_list')
                </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>

        </form>

        <div class="col-md-12 d-flex justify-content-between mb-2">
            <div class="download-area">
                <div class="d-inline-flex">
                    <div class="mr-2">

                        <form name="downloadForm" id="download_form" action="{{ $excel_download_url }}" method="POST">
                        @method($method)
                        @csrf

                            <input type="hidden" name="inout_date[start]" value="{{ old('inout_date.start', $search_condition_input_data['inout_date']['start'] ?? '') }}">
                            <input type="hidden" name="inout_date[end]" value="{{ old('inout_date.end', $search_condition_input_data['inout_date']['end'] ?? '') }}">
                            <input type="hidden" name="product_id" value="{{ $search_condition_input_data['product_id'] ?? '' }}">
                            <input type="hidden" name="warehouse_id" value="{{ $search_condition_input_data['warehouse_id'] ?? '' }}">

                            {{-- Excelダウンロードボタン --}}
                            <button type="submit" class="btn btn-success"
                                @if ($search_result['inventory_datas']->isEmpty()) disabled @endif>
                                <i class="fas fa-file-excel"></i> Excel
                            </button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 result-table-area table-responsive table-fixed" style="max-height: none !important;">
            <table class="table table-bordered table-responsive-org table-list mb-0">
                <thead class="thead-light">
                <tr class="text-center">
                    <th class="border-0 col-md-8" style="background-color: transparent;"></th>
                    <th style="width: 10%;">前月繰越数</th>
                    <th style="width: 10%;">残数
                        <span class="d-inline-block" title="前月繰越数+(入庫数-出庫数)">
                            <i class="fas fa-info-circle"></i>
                        </span>
                    </th>
                    <th style="width: 10%;">在庫数</th>
                </tr>
                </thead>
                <tr style="background-color: transparent;">
                    <td class="border-0" style="background-color: transparent;"></td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="前月繰越数">
                        {{ number_format($search_result['inventory_data_closing']) }}
                    </td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="残数">
                        {{ number_format($search_result['inventory_data_closing_stocks']) }}
                    </td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="在庫数">
                        {{ number_format($search_result['inventory_stock_total']) }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- 明細行 --}}
        <div class="col-md-12">
            <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                <div class="form-group d-md-inline-flex col-md-6 p-0 m-0">
                    {{ $search_result['inventory_datas']->appends($search_condition_input_data)->links() }}
                </div>
            </div>
            <div class="result-table-area table-responsive">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th style="width: 10%;">納品日または在庫データ入力日</th>
                        <th style="width: 10%;">得意先</th>
                        <th style="width: 10%;">支所</th>
                        <th style="width: 10%;">納品先</th>
                        <th style="width: 10%;">数量</th>
                        <th style="width: 10%;">担当者</th>
                        <th style="width: 10%;">備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['inventory_datas'] ?? [] as $inventory_data)
                        <tr>
                            {{-- 入出庫日 --}}
                            <td class="text-center align-middle" data-title="納品日または在庫データ入力日">
                                {{ Carbon::parse($inventory_data->inout_date)->format('Y/m/d') }}
                            </td>
                            {{-- 得意先 --}}
                            <td class="text-center align-middle" data-title="得意先">
                                @if($inventory_data->ordersReceived)
                                    {{ $inventory_data->ordersReceived->customer_name }}<br>
                                    (
                                    {{ $inventory_data->from_warehouse_name }} /
                                    {{ $inventory_data->to_warehouse_name }}
                                    )
                                @else
                                    {{ $inventory_data->from_warehouse_name }} /
                                    {{ $inventory_data->to_warehouse_name }}
                                @endif
                            </td>
                            {{-- 支所 --}}
                            <td class="text-center align-middle" data-title="支所">
                                @if($inventory_data->ordersReceived)
                                    {{ $inventory_data->ordersReceived->branch_name }}
                                @endif
                            </td>
                            {{-- 納品先 --}}
                            <td class="text-center align-middle" data-title="納品先">
                                @if($inventory_data->ordersReceived)
                                    {{ $inventory_data->ordersReceived->recipient_name }}
                                @endif
                            </td>
                            {{-- 数量 --}}
                            <td class="text-right align-middle" data-title="数量">
                                {{ number_format($inventory_data->quantity) }}
                            </td>
                            {{-- 担当者 --}}
                            <td class="text-center align-middle" data-title="担当者">
                                {{ $inventory_data->employee_name }}
                            </td>
                            {{-- 備考 --}}
                            <td class="text-center align-middle" data-title="備考">
                                {{ $inventory_data->note }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['inventory_datas']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- Search Product Modal --}}
    @component('components.search_product_modal')
        @slot('modal_id', 'search-product')
        @slot('products', $search_items['products'])
        @slot('onclick_select_product', "selectProductSearchProductModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/inventory/index.js') }}"></script>
    <script src="{{ mix('js/app/inventory/products_moving/index.js') }}"></script>
@endsection
