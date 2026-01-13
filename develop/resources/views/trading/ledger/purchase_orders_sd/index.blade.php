{{-- 仕入台帳参照画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')
@php
    use \App\Enums\PaymentMethodType;
@endphp

@php
    $headline = config('consts.title.trading.menu.orders_sd');
    $next_url = route('trading.ledger.purchase_orders_sd.index');
    $method = 'GET';
    /** @see MasterEmployeesConst */
    $maxlength_employee_code = MasterEmployeesConst::CODE_MAX_LENGTH;   // 担当者コード最大桁数
    /** @see MasterVendorsConst */
    $maxlength_supplier_code = MasterSuppliersConst::CODE_MAX_LENGTH;   // 仕入先コード最大桁数

    // デフォルトMAX日付・月
    $default_max_date = config('consts.default.common.default_max_date');
    $default_max_month = config('consts.default.common.default_max_month');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <form name="searchForm" action="{{ $next_url }}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">

            <input type="hidden" name="mode" value="search">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- 仕入日 --}}
                    @include('common.index.order_date')

                    {{-- 仕入先 --}}
                    @include('common.index.supplier_select_list', ['required_supplier' => true])
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
                        <form name="downloadForm" id="download_form" action="{{ route('trading.ledger.purchase_orders_sd.download_excel') }}" method="POST">
                        @method($method)
                        @csrf
                            <input type="hidden" name="supplier_id" value="{{ $search_condition_input_data['supplier_id'] ?? "" }}">
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? "") }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? "") }}">

                            {{-- Excelダウンロードボタン --}}
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </form>
                    </div>
                    <div class="mr-2">
                        <form name="show_pdf_form" id="show_pdf_form" action="{{ route('trading.ledger.purchase_orders_sd.show_pdf') }}" method="POST" target="_blank" rel="noopener noreferrer">
                        @method($method)
                        @csrf
                            <input type="hidden" name="supplier_id" value="{{ $search_condition_input_data['supplier_id'] ?? "" }}">
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? "") }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? "") }}">

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
            {{ $search_result['purchase_orders_sd'] ? $search_result['purchase_orders_sd']->appends($search_condition_input_data)->links() : null }}
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th>伝票日付</th>
                        <th>伝票番号</th>
                        <th>種別</th>
                        <th>商品コード</th>
                        <th>商品名</th>
                        <th>数量</th>
                        <th>単位</th>
                        <th>単価</th>
                        <th>仕入</th>
                        <th>消費税</th>
                        <th>支払</th>
                        <th>備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['purchase_orders_sd'] as $order_detail)
                        @if ($order_detail->order_kind == OrderType::PAYMENT)
                            @foreach($payment_method_types as $key => $payment_method_type)
                                {{-- 支払かつ、支払金額が 0 の場合は、明細行に出力しない --}}
                                @if(($key == PaymentMethodType::CASH && $order_detail->amount_cash == 0)
                                    || ($key == PaymentMethodType::CHECK && $order_detail->amount_check == 0)
                                    || ($key == PaymentMethodType::TRANSFER && $order_detail->amount_transfer == 0)
                                    || ($key == PaymentMethodType::BILL && $order_detail->amount_bill == 0)
                                    || ($key == PaymentMethodType::OFFSET && $order_detail->amount_offset == 0)
                                    || ($key == PaymentMethodType::DISCOUNT && $order_detail->amount_discount == 0)
                                    || ($key == PaymentMethodType::FEE && $order_detail->amount_fee == 0)
                                    || ($key == PaymentMethodType::OTHER && $order_detail->amount_other == 0)
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
                                        <a href="{{ route('trading.payments.edit', $order_detail->order_number) }}">
                                            {{ $order_detail->order_number }}
                                        </a>
                                    </td>
                                    {{-- 種別 --}}
                                    <td class="text-center" data-title="種別">
                                        {{ OrderType::getDescription($order_detail->order_kind) }}
                                    </td>
                                    {{--商品コード--}}
                                    <td class="text-center" data-title="商品コード">
                                        {{ $order_detail->product_code }}
                                    </td>
                                    {{--商品名--}}
                                    <td class="text-left" data-title="商品名">
                                        @if($key==PaymentMethodType::CASH)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @elseif($key==PaymentMethodType::CHECK)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @elseif($key==PaymentMethodType::TRANSFER)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @elseif($key==PaymentMethodType::BILL)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @elseif($key==PaymentMethodType::OFFSET)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @elseif($key==PaymentMethodType::DISCOUNT)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @elseif($key==PaymentMethodType::FEE)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @elseif($key==PaymentMethodType::OTHER)
                                            {{ PaymentMethodType::getDescription($key) }}
                                        @endif

                                    </td>
                                    {{--数量--}}
                                    <td class="text-right" data-title="数量">
                                        @if ($order_detail->order_kind === OrderType::PURCHASE)
                                            {{ number_format($order_detail->quantity) ?? null }}
                                        @endif
                                    </td>
                                    {{--単位--}}
                                    <td class="text-center" data-title="単位">
                                        {{ $order_detail->unit_name }}
                                    </td>
                                    {{--単価--}}
                                    <td class="text-right" data-title="単価">
                                        @if ($order_detail->order_kind === OrderType::PURCHASE)
                                            {{ number_format($order_detail->unit_price) ?? null }}
                                        @endif
                                    </td>
                                    {{-- 仕入 --}}
                                    <td class="text-right" data-title="仕入">
                                        @if ($order_detail->order_kind === OrderType::PURCHASE)
                                            {{ number_format($order_detail->purchase_total) ?? null }}
                                        @endif
                                    </td>
                                    {{-- 消費税 --}}
                                    <td class="text-right" data-title="消費税">
                                        @if ($order_detail->order_kind === OrderType::PURCHASE)
                                            {{ number_format($order_detail->sub_total_tax) ?? null }}
                                        @endif
                                    </td>
                                    {{-- 支払 --}}
                                    <td class="text-right" data-title="支払">
                                            @if($key==PaymentMethodType::CASH)
                                                {{ number_format($order_detail->amount_cash) }}
                                            @elseif($key==PaymentMethodType::CHECK)
                                                {{ number_format($order_detail->amount_check) }}
                                            @elseif($key==PaymentMethodType::TRANSFER)
                                                {{ number_format($order_detail->amount_transfer) }}
                                            @elseif($key==PaymentMethodType::BILL)
                                                {{ number_format($order_detail->amount_bill) }}
                                            @elseif($key==PaymentMethodType::OFFSET)
                                                {{ number_format($order_detail->amount_offset) }}
                                            @elseif($key==PaymentMethodType::DISCOUNT)
                                                {{ number_format($order_detail->amount_discount) }}
                                            @elseif($key==PaymentMethodType::FEE)
                                                {{ number_format($order_detail->amount_fee) }}
                                            @elseif($key==PaymentMethodType::OTHER)
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
                                    <a href="{{ route('trading.purchase_orders.edit', $order_detail->order_number) }}">
                                        {{ $order_detail->order_number }}
                                    </a>
                                </td>
                                {{-- 種別 --}}
                                <td class="text-center" data-title="種別">
                                    {{ OrderType::getDescription($order_detail->order_kind) }}
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
                                    @if ($order_detail->order_kind === OrderType::PURCHASE)
                                        {{ number_format($order_detail->quantity) ?? null }}
                                    @endif
                                </td>
                                {{--単位--}}
                                <td class="text-center" data-title="単位">
                                    {{ $order_detail->unit_name }}
                                </td>
                                {{--単価--}}
                                <td class="text-right" data-title="単価">
                                    @if ($order_detail->order_kind === OrderType::PURCHASE)
                                        {{ number_format($order_detail->unit_price) ?? null }}
                                    @endif
                                </td>
                                {{-- 仕入 --}}
                                <td class="text-right" data-title="仕入">
                                    @if ($order_detail->order_kind === OrderType::PURCHASE)
                                        {{ number_format($order_detail->purchase_total) ?? null }}
                                    @endif
                                </td>
                                {{-- 消費税 --}}
                                <td class="text-right" data-title="消費税">
                                    @if ($order_detail->order_kind === OrderType::PURCHASE)
                                        {{ number_format($order_detail->sub_total_tax) ?? null }}
                                    @endif
                                </td>
                                {{-- 支払 --}}
                                <td class="text-right" data-title="支払">
                                    @if ($order_detail->order_kind === OrderType::PAYMENT)
                                        {{ number_format($order_detail->payment) ?? null }}
                                    @endif
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
            {{ $search_result['purchase_orders_sd'] ? $search_result['purchase_orders_sd']->appends($search_condition_input_data)->links() : null }}
        </div>
    </div>

    {{-- Search Employee Modal --}}
    @component('components.search_employee_modal')
        @slot('modal_id', 'search-employee')
        @slot('employees', $search_items['employees'])
        @slot('onclick_select_employee', "selectEmployeeSearchEmployeeModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/trading/index.js') }}"></script>
    <script src="{{ mix('js/app/trading/ledger/purchase_orders_sd/index.js') }}"></script>
@endsection
