{{-- 在庫データ一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    use Carbon\Carbon;

    $headline = config('consts.title.inventory.menu.index');
    $next_url = route('inventory.inventory_datas.index');
    $excel_download_url = route('inventory.inventory_datas.download_excel');
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
                    {{-- 入出庫日 --}}
                    @include('common.index.inout_date')

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- どこから(from) --}}
                        @component('components.index.warehouse_select_list')
                            @slot('title', 'どこから')
                            @slot('warehouse_id', 'from_warehouse_id')
                            @slot('search_items', $search_items)
                            @slot('search_condition_input_data', $search_condition_input_data)
                        @endcomponent
                        {{-- どこへ(to) --}}
                        @component('components.index.warehouse_select_list')
                            @slot('title', 'どこへ')
                            @slot('warehouse_id', 'to_warehouse_id')
                            @slot('search_items', $search_items)
                            @slot('search_condition_input_data', $search_condition_input_data)
                        @endcomponent
                    </div>
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
                            <input type="hidden" name="from_warehouse_id" value="{{ $search_condition_input_data['from_warehouse_id'] ?? '' }}">
                            <input type="hidden" name="to_warehouse_id" value="{{ $search_condition_input_data['to_warehouse_id'] ?? '' }}">

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

        {{-- 明細行 --}}
        <div class="col-md-12">
            <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                <div class="form-group d-md-inline-flex col-md-6 p-0 m-0">
                    {{ $search_result['inventory_datas']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    {{-- 新規登録ボタン --}}
                    @component('components.index.create_button')
                        @slot('route', route('inventory.inventory_datas.create'))
                    @endcomponent
                </div>
            </div>
            <div class="result-table-area table-responsive">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th style="width: 2%;">
                            <a class="centralClose">-</a>
                            <a class="centralOpen float-none" style="display: none;">+</a>
                        </th>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 8%;">担当者</th>
                        <th style="width: 20%;">入出庫日</th>
                        <th style="width: 35%;">どこから</th>
                        <th style="width: 35%;">どこへ</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['inventory_datas'] ?? [] as $inventory_data)
                        <tr>
                            <th class="text-center align-middle pc-no-display"></th>
                            <td class="text-center">
                                <a class="close">-</a>
                                <a class="open float-none" style="display: none;">+</a>
                            </td>
                            {{-- ID --}}
                            <td class="text-center" data-title="ID">
                                <a href="{{ route('inventory.inventory_datas.edit', $inventory_data->id) }}">
                                    {{ $inventory_data->id }}
                                </a>
                            </td>
                            {{-- 担当者 --}}
                            <td class="text-center" data-title="担当者">
                                {{ $inventory_data->employee_name }}
                            </td>
                            {{-- 入出庫日 --}}
                            <td class="text-center" data-title="入出庫日">
                                {{ Carbon::parse($inventory_data->inout_date)->format('Y/m/d') }}
                            </td>
                            {{-- どこから --}}
                            <td class="text-center" data-title="どこから">
                                {{ $inventory_data->mWarehouseFrom->name }}
                            </td>
                            {{-- どこへ --}}
                            <td class="text-center" data-title="どこへ">
                                {{ $inventory_data->mWarehouseTo->name }}
                            </td>
                        </tr>

                        <tr class="detail">
                            <td colspan="10">
                                <table class="table table-fixed mb-1 table-th-white"
                                       id="order_products_table"
                                       style="max-height: none !important;">
                                    <thead class="thead-light text-center border-md-silver">
                                    <tr class="d-none d-md-table-row">
                                        <th style="width: 3%;">No.</th>
                                        <th style="width: 34%;">商品</th>
                                        <th style="width: 13%;">数量</th>
                                        <th style="width: 20%;">備考</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($inventory_data->inventoryDataDetail as $key => $detail)
                                        <tr>
                                            <th class="text-center align-middle text-dark border-secondary border-md-none">
                                                {{ ++$key }}
                                            </th>
                                            <td class="text-left align-middle border-secondary border-top-0 border-md-none"
                                                data-title="商品">
                                                {{ $detail->product_name }}
                                            </td>
                                            <td class="text-right align-middle border-secondary border-top-0 border-md-none"
                                                data-title="数量">
                                                {{ number_format($detail->quantity) }}
                                            </td>
                                            <td class="text-left align-middle border-secondary border-top-0 border-md-none"
                                                data-title="備考">
                                                {{ $detail->note }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
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
    <script src="{{ mix('js/app/inventory/inventory_datas/index.js') }}"></script>
@endsection
