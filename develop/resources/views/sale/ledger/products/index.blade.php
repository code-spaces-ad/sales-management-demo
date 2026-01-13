{{-- 商品台帳画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.sale.menu.sale_ledger_submenu.products');
    $next_url = route('sale.ledger.products');
    $excel_download_url = route('sale.ledger.products.download_excel');
    $show_pdf_url = route('sale.ledger.products.show_pdf');
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

                    {{-- 商品 --}}
                    @include('common.index.product_select_list', ['required_product' => true])
                </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>
        </form>

        <div class="col-md-12 result-table-area table-responsive table-fixed" style="max-height: none !important;">
            <table class="table table-bordered table-responsive-org table-list mb-0">
                <thead class="thead-light">
                <tr class="text-center">
                    <th class="border-0 col-md-9" style="background-color: transparent;"></th>
                    <th>売上数量</th>
                    <th>仕入数量</th>
                    <th>前月在庫数</th>
                </tr>
                </thead>
                <tr style="background-color: transparent;">
                    <td class="border-0 col-md-9" style="background-color: transparent;"></td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="売上数量">
                        {{ number_format($search_result['sales_quantity'] ?? 0) }}
                    </td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="仕入数量">
                        {{ number_format($search_result['purchase_quantity'] ?? 0) }}
                    </td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="前月在庫数">
                        {{ number_format($search_result['ledger_stocks'] ?? 0) }}
                    </td>
                </tr>
            </table>
        </div>

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
                            <input type="hidden" name="product_id" value="{{ $search_condition_input_data['product_id'] ?? '' }}">

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
            {{ $search_result['order_details']->appends($search_condition_input_data)->links() }}
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th>伝票日付</th>
                        <th>伝票番号</th>
                        <th>種別</th>
                        <th>取引先コード</th>
                        <th>取引先</th>
                        <th>支所名</th>
                        <th>単位</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['order_details'] as $order_detail)
                        <tr>
                            <th class="text-center align-middle pc-no-display"></th>
                            {{--伝票日付--}}
                            <td class="text-center" data-title="伝票日付">
                                {{ $order_detail->order_date_slash }}
                            </td>
                            {{--伝票番号--}}
                            <td class="text-center" data-title="伝票番号">
                                @if ($order_detail->order_kind == OrderType::SALES)
                                    {{-- 売上伝票へのリンク --}}
                                    <a href="{{ route('sale.orders.edit', $order_detail->order_id) }}">
                                        {{ $order_detail->order_number_zero_fill }}
                                    </a>
                                @elseif ($order_detail->order_kind == OrderType::PURCHASE)
                                    {{-- 仕入伝票へのリンク --}}
                                    <a href="{{ route('trading.purchase_orders.edit', $order_detail->order_id) }}">
                                        {{ $order_detail->order_number_zero_fill }}
                                    </a>
                                @else
                                    伝票番号無し
                                @endif
                            </td>
                            {{-- 種別 --}}
                            <td class="text-center" data-title="種別">
                                {{ OrderType::getDescription($order_detail->order_kind) }}
                            </td>
                            {{--取引先コード--}}
                            <td class="text-center" data-title="取引先コード">
                                @if ($order_detail->order_kind === OrderType::SALES)
                                    {{ $order_detail->customer_code_zero_fill }}
                                @elseif ($order_detail->order_kind === OrderType::PURCHASE)
                                    {{ $order_detail->supplier_code_zero_fill }}
                                @else
                                    コード無し
                                @endif
                            </td>
                            {{--取引先--}}
                            <td class="text-left" data-title="取引先">
                                @if ($order_detail->order_kind === OrderType::SALES)
                                    {{ $order_detail->customer_name }}
                                @elseif ($order_detail->order_kind === OrderType::PURCHASE)
                                    {{ $order_detail->supplier_name }}
                                @else
                                    取引先無し
                                @endif
                            </td>
                            {{--支所名--}}
                            <td class="text-center" data-title="支所名">
                                @if ($order_detail->order_kind === OrderType::SALES)
                                    {{ $order_detail->branch_name ?? null }}
                                @endif
                            </td>
                            {{--単位--}}
                            <td class="text-center" data-title="単位">
                                {{ $order_detail->unit_name }}
                            </td>
                            {{--単価--}}
                            <td class="text-right" data-title="単価">
                                {{ number_format($order_detail->unit_price, $order_detail->unit_price_decimal_digit ?? 0) }}
                            </td>
                            {{--数量--}}
                            <td class="text-right" data-title="数量">
                                {{ number_format($order_detail->quantity, $order_detail->quantity_decimal_digit ?? 0) }}
                            </td>
                            {{--備考--}}
                            <td class="text-left" data-title="備考">
                                {{ $order_detail->note }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['order_details']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/sale/index.js') }}"></script>
    <script src="{{ mix('js/app/sale/ledger/products/index.js') }}"></script>
@endsection
