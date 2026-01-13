{{-- 金種別入金一覧表画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.sale.menu.sale_ledger_submenu.deposits');
    $next_url = route('sale.ledger.deposits');
    $excel_download_url = route('sale.ledger.deposits.download_excel');
    $show_pdf_url = route('sale.ledger.deposits.show_pdf');
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
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? ' ') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? ' ') }}">

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
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start']) ?? ' ' }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end']) ?? ' ' }}">

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
            <div class="result-table-area table-responsive table-fixed" style="max-height: 425px;">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th>得意先コード</th>
                        <th>得意先</th>
                        <th>現金</th>
                        <th>小切手</th>
                        <th>振込</th>
                        <th>手形</th>
                        <th>相殺</th>
                        <th>値引</th>
                        <th>手数料</th>
                        <th>その他</th>
                        <th>合計</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['order_details'] as $order_detail)
                        <tr>
                            <th class="text-center align-middle pc-no-display"></th>
                            {{-- 得意先コード --}}
                            <td class="text-center" data-title="得意先コード">
                                {{ $order_detail->customer_code }}
                            </td>
                            {{-- 得意先 --}}
                            <td class="text-left" data-title="得意先">
                                {{ $order_detail->c_name }}
                            </td>
                            {{-- 現金 --}}
                            <td class="text-right" data-title="現金">
                                {{ $order_detail->amount_cash }}
                            </td>
                            {{-- 小切手 --}}
                            <td class="text-right" data-title="小切手">
                                {{ $order_detail->amount_check }}
                            </td>
                            {{-- 振込 --}}
                            <td class="text-right" data-title="振込">
                                {{ $order_detail->amount_transfer }}
                            </td>
                            {{-- 手形 --}}
                            <td class="text-right" data-title="手形">
                                {{ $order_detail->amount_bill }}
                            </td>
                            {{-- 相殺 --}}
                            <td class="text-right" data-title="相殺">
                                {{ $order_detail->amount_offset }}
                            </td>
                            {{-- 値引 --}}
                            <td class="text-right" data-title="値引">
                                {{ $order_detail->amount_discount }}
                            </td>
                            {{-- 手数料 --}}
                            <td class="text-right" data-title="手数料">
                                {{ $order_detail->amount_fee }}
                            </td>
                            {{-- その他 --}}
                            <td class="text-right" data-title="その他">
                                {{ $order_detail->amount_other }}
                            </td>
                            {{-- 合計 --}}
                            <td class="text-right" data-title="合計">
                                {{ $order_detail->total_deposit }}
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
    <script src="{{ mix('js/app/sale/ledger/deposits/index.js') }}"></script>
@endsection
