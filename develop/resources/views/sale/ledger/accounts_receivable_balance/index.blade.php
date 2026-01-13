{{-- 売掛台帳画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.sale.menu.sale_ledger_submenu.accounts_receivable_balance');
    $next_url = route('sale.ledger.accounts_receivable_balance');
    $excel_download_url = route('sale.ledger.accounts_receivable_balance.download_excel');
    $show_pdf_url = route('sale.ledger.accounts_receivable_balance.show_pdf');
    $method = 'GET';

    /** @see MasterCustomersConst */
    $maxlength_customer_code = MasterCustomersConst::CODE_MAX_LENGTH;   // 得意先コード最大桁数
    $min_customer_code = MasterCustomersConst::CODE_MIN_VALUE;   // 得意先コード最小値
    // デフォルトMAX日付
    $default_max_date = config('consts.default.common.default_max_date');
    $default_max_month = config('consts.default.common.default_max_month');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <form name="searchForm" action="{{$next_url}}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">
            <input type="hidden" name="mode" value="search">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- 出力期間 --}}
                    @include('common.index.order_date')

                    {{-- 得意先 --}}
                    @include('common.index.customer_select_list', ['required_customer' => true])
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
                    <th class="border-0 col-md-11" style="background-color: transparent;"></th>
                    <th>前月請求額</th>
                </tr>
                </thead>
                <tr style="background-color: transparent;">
                    <td class="border-0 col-md-11" style="background-color: transparent;"></td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="前月請求額">
                        {{ number_format($search_result['carryover']->charge_total ?? 0) }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-md-12 d-flex justify-content-between mb-2">
            <div class="download-area">
                <div class="d-inline-flex">
                    <div class="mr-2">
                        <form name="downloadForm" action="{{$excel_download_url}}" id="download_form" method="POST">
                        @method($method)
                            <input type="hidden" name="customer_id" value="{{ $search_condition_input_data['customer_id'] ?? '' }}">
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start',$search_condition_input_data['order_date']['start'] ?? '') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.start',$search_condition_input_data['order_date']['end'] ?? '') }}">

                            {{-- Excelダウンロードボタン --}}
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>

                        </form>
                    </div>
                    <div class="mr-2">
                        <form name="showPdfForm" action="{{$show_pdf_url}}" id="show_pdf_form" method="POST" target="_blank" rel="noopener noreferrer">
                        @method($method)
                            <input type="hidden" name="customer_id" value="{{ $search_condition_input_data['customer_id'] ?? '' }}">
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start',['order_date']['start'] ?? '') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end',$search_condition_input_data['order_date']['end'] ?? '') }}">

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
                        <th>支所名</th>
                        <th>商品コード</th>
                        <th>商品名</th>
                        <th>数量</th>
                        <th>単位</th>
                        <th>単価</th>
                        <th>金額</th>
                        <th>消費税</th>
                        <th>入金</th>
                        <th>備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['order_details'] as $order_detail)
                        @if ($order_detail->order_kind == OrderType::DEPOSIT)
                            @foreach($deposit_method_types as $key => $deposit_method_type)
                                {{-- 入金かつ、入金金額が 0 の場合は、明細行に出力しない --}}
                                @if(($key == DepositMethodType::CASH && $order_detail->amount_cash == 0)
                                    || ($key == DepositMethodType::CHECK && $order_detail->amount_check == 0)
                                    || ($key == DepositMethodType::TRANSFER && $order_detail->amount_transfer == 0)
                                    || ($key == DepositMethodType::BILL && $order_detail->amount_bill == 0)
                                    || ($key == DepositMethodType::OFFSET && $order_detail->amount_offset == 0)
                                    || ($key == DepositMethodType::DISCOUNT && $order_detail->amount_discount == 0)
                                    || ($key == DepositMethodType::FEE && $order_detail->amount_fee == 0)
                                    || ($key == DepositMethodType::OTHER && $order_detail->amount_other == 0)
                                )
                                    @continue
                                @endif
                                <tr>
                                    <th class="text-center align-middle pc-no-display"></th>
                                    {{--伝票日付--}}
                                    <td class="text-center" data-title="伝票日付">
                                        {{ $order_detail->order_date }}
                                    </td>
                                    {{-- 伝票番号 --}}
                                    <td class="text-center" data-title="伝票番号">
                                        {{-- 入金伝票へのリンク --}}
                                        <a href="{{ route('sale.deposits.edit', $order_detail->order_id) }}">
                                            {{ $order_detail['order_number'] }}
                                        </a>
                                    </td>
                                    {{-- 種別 --}}
                                    <td class="text-center" data-title="種別">
                                        {{ OrderType::getDescription($order_detail->order_kind) }}
                                    </td>
                                    {{--支所名--}}
                                    <td class="text-center" data-title="支所名">
                                        {{ $order_detail['branch_n'] ?? null }}
                                    </td>
                                    {{--商品コード--}}
                                    <td class="text-center" data-title="商品コード">
                                        {{ $order_detail->product_code }}
                                    </td>
                                    {{--商品名--}}
                                    <td class="text-left" data-title="商品名">
                                        @if($key==DepositMethodType::CASH)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @elseif($key==DepositMethodType::CHECK)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @elseif($key==DepositMethodType::TRANSFER)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @elseif($key==DepositMethodType::BILL)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @elseif($key==DepositMethodType::OFFSET)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @elseif($key==DepositMethodType::DISCOUNT)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @elseif($key==DepositMethodType::FEE)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @elseif($key==DepositMethodType::OTHER)
                                            {{ DepositMethodType::getDescription($key) }}
                                        @endif
                                    </td>
                                    {{--数量--}}
                                    <td class="text-right" data-title="数量">
                                        @if($order_detail->quantity != null)
                                            {{ number_format($order_detail->quantity, $order_detail->quantity_decimal_digit ?? 0) }}
                                        @endif
                                    </td>
                                    {{--単位--}}
                                    <td class="text-center" data-title="単位">
                                        {{ $order_detail->unit_name }}
                                    </td>
                                    {{--単価--}}
                                    <td class="text-right" data-title="単価">
                                        @if($order_detail->unit_price != null)
                                            {{ number_format($order_detail->unit_price, $order_detail->unit_price_decimal_digit ?? 0) }}
                                        @endif
                                    </td>
                                    {{-- 金額 --}}
                                    <td class="text-right" data-title="金額">
                                        @if (!($order_detail->order_kind == OrderType::DEPOSIT))
                                            {{ number_format($order_detail->sub_total) }}
                                        @endif
                                    </td>
                                    {{-- 消費税 --}}
                                    <td class="text-right" data-title="消費税">
                                        @if (!($order_detail->order_kind == OrderType::DEPOSIT))
                                            {{ number_format($order_detail->sub_total_tax) }}
                                        @endif
                                    </td>
                                    {{-- 入金 --}}
                                    <td class="text-right" data-title="入金">
                                        @if($key==DepositMethodType::CASH)
                                            {{ number_format($order_detail->amount_cash) }}
                                        @elseif($key==DepositMethodType::CHECK)
                                            {{ number_format($order_detail->amount_check) }}
                                        @elseif($key==DepositMethodType::TRANSFER)
                                            {{ number_format($order_detail->amount_transfer) }}
                                        @elseif($key==DepositMethodType::BILL)
                                            {{ number_format($order_detail->amount_bill) }}
                                        @elseif($key==DepositMethodType::OFFSET)
                                            {{ number_format($order_detail->amount_offset) }}
                                        @elseif($key==DepositMethodType::DISCOUNT)
                                            {{ number_format($order_detail->amount_discount) }}
                                        @elseif($key==DepositMethodType::FEE)
                                            {{ number_format($order_detail->amount_fee) }}
                                        @elseif($key==DepositMethodType::OTHER)
                                            {{ number_format($order_detail->amount_other) }}
                                        @endif
                                    </td>
                                    {{--備考--}}
                                    <td class="text-left" data-title="備考">
                                        {{ $order_detail->note }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <th class="text-center align-middle pc-no-display"></th>
                                {{--伝票日付--}}
                                <td class="text-center" data-title="伝票日付">
                                    {{ $order_detail->order_date }}
                                </td>
                                {{-- 伝票番号 --}}
                                <td class="text-center" data-title="伝票番号">
                                    {{-- 売上伝票へのリンク --}}
                                    <a href="{{ route('sale.orders.edit', $order_detail->order_id) }}">
                                        {{ $order_detail['order_number'] }}
                                    </a>
                                </td>
                                {{-- 種別 --}}
                                <td class="text-center" data-title="種別">
                                    {{ OrderType::getDescription($order_detail->order_kind) }}
                                </td>
                                {{--支所名--}}
                                <td class="text-center" data-title="支所名">
                                    {{ $order_detail['branch_n'] ?? null }}
                                </td>
                                {{--商品コード--}}
                                <td class="text-center" data-title="商品コード">
                                    {{ $order_detail->product_code }}
                                </td>
                                {{--商品名--}}
                                <td class="text-left" data-title="商品名">
                                    {{ $order_detail->product_name }}
                                </td>
                                {{--数量--}}
                                <td class="text-right" data-title="数量">
                                    @if($order_detail->quantity != null)
                                        {{ number_format($order_detail->quantity, $order_detail->quantity_decimal_digit ?? 0) }}
                                    @endif
                                </td>
                                {{--単位--}}
                                <td class="text-center" data-title="単位">
                                    {{ $order_detail->unit_name }}
                                </td>
                                {{--単価--}}
                                <td class="text-right" data-title="単価">
                                    @if($order_detail->unit_price != null)
                                        {{ number_format($order_detail->unit_price, $order_detail->unit_price_decimal_digit ?? 0) }}
                                    @endif
                                </td>
                                {{-- 金額 --}}
                                <td class="text-right" data-title="金額">
                                    @if (!($order_detail->order_kind == OrderType::DEPOSIT))
                                        {{ number_format($order_detail->sub_total) }}
                                    @endif
                                </td>
                                {{-- 消費税 --}}
                                <td class="text-right" data-title="消費税">
                                    @if (!($order_detail->order_kind == OrderType::DEPOSIT))
                                        {{ number_format($order_detail->sub_total_tax) }}
                                    @endif
                                </td>
                                {{-- 入金 --}}
                                <td class="text-right" data-title="入金">

                                </td>
                                {{--備考--}}
                                <td class="text-left" data-title="備考">
                                    {{ $order_detail->note }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['order_details']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/sale/index.js') }}"></script>
    <script src="{{ mix('js/app/sale/ledger/accounts_receivable_balance/index.js') }}"></script>
@endsection
