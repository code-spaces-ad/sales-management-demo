{{-- 種別累計売上表画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.sale.menu.sale_ledger_submenu.categories');
    $next_url = route('sale.ledger.categories');
    $excel_download_url = route('sale.ledger.categories.download_excel');
    $show_pdf_url = route('sale.ledger.categories.show_pdf');
    $method = 'GET';

    /** @see MasterProductsConst */
    $maxlength_product_code = MasterProductsConst::CODE_MAX_LENGTH;   // 商品コード最大桁数
    $min_product_code = MasterProductsConst::CODE_MIN_VALUE;   // 商品コード最小値

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

                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start',$search_condition_input_data['order_date']['start'] ?? ' ') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end',$search_condition_input_data['order_date']['end'] ?? ' ') }}">

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
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? ' ') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? ' ') }}">

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
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th colspan="1" class="border-0" style="background-color: transparent;"></th>
                        <th>肥料合計</th>
                        <th>農薬合計</th>
                        <th>資材合計</th>
                        <th>種子合計</th>
                        <th>その他合計</th>
                        <th>売上合計</th>
                    </tr>
                    <tr style="background-color: transparent;">
                        <td colspan="1" class="border-0" style="background-color: transparent;"></td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="肥料合計">
                            {{ number_format($search_result['category_total']['fertilizer_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="農薬合計">
                            {{ number_format($search_result['category_total']['pesticide_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="資材合計">
                            {{ number_format($search_result['category_total']['material_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="種子合計">
                            {{ number_format($search_result['category_total']['seed_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="その他合計">
                            {{ number_format($search_result['category_total']['another_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="売上合計">
                            {{ number_format($search_result['category_total']['all_total'] ?? 0) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0" style="height: 10px"></td>
                    </tr>
                    <tr class="text-center">
                        <th scope="col">伝票日付</th>
                        <th scope="col">肥料</th>
                        <th scope="col">農薬</th>
                        <th scope="col">資材</th>
                        <th scope="col">種子</th>
                        <th scope="col">その他</th>
                        <th scope="col">日計</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['sales_orders'] as $order)
                        <tr>
                            <td class="text-center align-middle" data-title="伝票日付">
                                {{ $order->order_date_slash }}
                            </td>
                            <td class="text-right align-middle" data-title="肥料">
                                {{ number_format($order->fertilizer) }}
                            </td>
                            <td class="text-right align-middle" data-title="農薬">
                                {{ number_format($order->pesticide) }}
                            </td>
                            <td class="text-right align-middle" data-title="資材">
                                {{ number_format($order->material) }}
                            </td>
                            <td class="text-right align-middle" data-title="種子">
                                {{ number_format($order->seed) }}
                            </td>
                            <td class="text-right align-middle" data-title="その他">
                                {{ number_format($order->another) }}
                            </td>
                            <td class="text-right align-middle" data-title="日計">
                                {{ number_format($order->day_total) }}
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
    <script src="{{ mix('js/app/sale/ledger/categories/index.js') }}"></script>
@endsection
