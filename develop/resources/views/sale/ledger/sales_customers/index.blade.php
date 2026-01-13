{{-- 得意先別売上表画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.sale.menu.sale_ledger_submenu.sales_customers');
    $next_url = route('sale.ledger.sales_customers');
    $excel_download_url = route('sale.ledger.sales_customers.download_excel');
    $show_pdf_url = route('sale.ledger.sales_customers.show_pdf');
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

                            {{-- Excelダウンロードボタン --}}
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </form>
                    </div>
                    <div class="mr-2">
                        <form name="showPdfForm" id="show_pdf_form" action="{{ $show_pdf_url }}" method="POST" target="_blank" rel="noopener noreferrer">
                        @method($method)
                        @csrf
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? '') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? '') }}">

                            {{-- PDF表示ボタン --}}
                            <button class="btn btn-danger" type="submit">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="result-table-area table-responsive table-fixed">
                <table class="table table-bordered">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th>得意先コード</th>
                        <th>得意先</th>
                        <th>売上金額</th>
                        <th>粗利率(％)</th>
                        <th>構成比(％)</th>
                        <th>順位</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($search_result['order_details'] as $key => $val)
                            <tr>
                                {{--得意先コード--}}
                                <td class="text-center">
                                    {{ $val->customer_code }}
                                </td>
                                {{--得意先--}}
                                <td class="text-left">
                                    {{ $val->c_name }}
                                </td>
                                {{-- 売上金額 --}}
                                <td class="text-right">
                                    {{ number_format($val->sales_total) }}
                                </td>
                                {{-- 粗利率 --}}
                                <td class="text-right">
                                    {{ $val->gross_profit_margin }}
                                </td>
                                {{-- 構成比 --}}
                                <td class="text-right">
                                    {{ $val->composition_ratio }}
                                </td>
                                {{-- 順位 --}}
                                <td class="text-right">
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
    <script src="{{ mix('js/app/sale/ledger/sales_customers/index.js') }}"></script>
@endsection
