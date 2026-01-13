{{-- 商品別売上表画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.sale.menu.sale_ledger_submenu.sales_products');
    $next_url = route('sale.ledger.sales_products');
    $excel_download_url = route('sale.ledger.sales_products.download_excel');
    $show_pdf_url = route('sale.ledger.sales_products.show_pdf');
    $method = 'GET';

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
                    {{-- 出力期間 --}}
                    @include('common.index.order_date')
                </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>
        </form>

        <div class="col-md-10 d-flex justify-content-between mb-2">
            <div class="download-area">
                <div class="d-inline-flex">
                    <div class="mr-2">
                        <form name="downloadForm" id="download_form" action="{{ $excel_download_url }}" method="POST">
                        @method($method)
                        @csrf
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? '') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? '') }}">
                            <input type="hidden" name="product_id" value="{{ $search_condition_input_data['product_id'] ?? '' }}">

                            {{-- Excelダウンロードボタン --}}
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </form>
                    </div>
                    <div class="mr-2">
                        <form name="showPdfForm" id="show_pdf_form" action="{{ $show_pdf_url }}" method="POST" target="_blank" rel="noopener noreferrer">
                        @method($method)
                        @csrf
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? '') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['end'] ?? '') }}">
                            <input type="hidden" name="product_id" value="{{ $search_condition_input_data['product_id'] ?? '' }}">

                            {{-- PDF表示ボタン --}}
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="result-table-area table-responsive table-fixed">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th style="width: 10%">商品コード</th>
                        <th style="width: 35%">商品名</th>
                        <th style="width: 10%">売上数量</th>
                        <th style="width: 6%">単位</th>
                        <th style="width: 13%">売上金額</th>
                        <th style="width: 10%">粗利率(％)</th>
                        <th style="width: 10%">構成比(％)</th>
                        <th style="width: 6%">順位</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['order_details'] as $key => $val)
                        <tr>
                            <th class="text-center align-middle pc-no-display"></th>
                            {{-- 商品コード --}}
                            <td class="text-center" data-title="商品コード">
                                {{ $val->product_code }}
                            </td>
                            {{-- 商品名 --}}
                            <td class="text-left" data-title="商品名">
                                {{ $val->product_name }}
                            </td>
                            {{-- 売上数量 --}}
                            <td class="text-right" data-title="売上数量">
                                {{ number_format($val->quantity, $val->quantity_decimal_digit ?? 0) }}
                            </td>
                            {{-- 単位 --}}
                            <td class="text-center" data-title="単位">
                                {{ $val->unit_name }}
                            </td>
                            {{-- 売上金額 --}}
                            <td class="text-right" data-title="売上金額">
                                {{ number_format($val->sales_total) }}
                            </td>
                            {{-- 粗利率 --}}
                            <td class="text-right" data-title="粗利率">
                                {{ $val->gross_profit_margin }}
                            </td>
                            {{-- 構成比 --}}
                            <td class="text-right" data-title="構成比">
                                {{ $val->composition_ratio }}
                            </td>
                            {{-- 順位 --}}
                            <td class="text-right" data-title="順位">
                                {{ $val->rank }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/sale/index.js') }}"></script>
    <script src="{{ mix('js/app/sale/ledger/sales_products/index.js') }}"></script>
@endsection
