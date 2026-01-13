{{-- 支払伝票一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.trading.menu.payment.index');
    $next_url = route('trading.payments.index');
    $method = 'GET';
    /** @see MasterSuppliersConst */
    $maxlength_supplier_code = MasterSuppliersConst::CODE_MAX_LENGTH;   // 仕入先コード最大桁数
    $min_supplier_code = MasterSuppliersConst::CODE_MIN_VALUE;   // 仕入先コード最小値

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
                {{-- 伝票番号 --}}
                @component('components.index.order_number')
                    @slot('title', '伝票番号')
                    @slot('search_condition_input_data', $search_condition_input_data)
                    @slot('order_number', 'order_number')
                @endcomponent

                {{-- 伝票種別 --}}
                @include('common.index.transaction_type_checkbox')

                {{-- 伝票日付 --}}
                @include('common.index.order_date')

                {{-- 仕入先 --}}
                @include('common.index.supplier_select_list')

                <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                    {{-- 部門 --}}
                    @include('common.index.department_select_list')

                    {{-- 事業所 --}}
                    @include('common.index.office_facility_select_list')
                </div>
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
                    <th class="border-0" style="background-color: transparent; width: 2%;"></th>
                    <th class="border-0 col-md-7" style="background-color: transparent;"></th>
                    <th class="col-md-1">支払合計額合計</th>
                    <th class="col-md-1">支払額合計</th>
                    <th class="col-md-1">調整額合計</th>
                    <th class="border-0 col-md-2" style="background-color: transparent;"></th>
                </tr>
                <tr style="background-color: transparent;">
                    <td class="border-0" style="background-color: transparent;"></td>
                    <td class="border-0" style="background-color: transparent;"></td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="支払合計額合計">
                        {{ number_format($search_result['payments_total']['payment_amount'] ?? 0) }}
                    </td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="支払額合計">
                        {{ number_format($search_result['payments_total']['payment'] ?? 0) }}
                    </td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="調整額合計">
                        {{ number_format($search_result['payments_total']['adjust_amount_total'] ?? 0) }}
                    </td>
                    <td class="border-0" style="background-color: transparent;"></td>
                </tr>
                <tr>
                    <td class="border-0" style="height: 10px"></td>
                </tr>
            </table>
        </div>

        <div class="col-md-12">
            <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                <div class="form-group d-md-inline-flex col-md-6 p-0 m-0">
                    {{ $search_result['payments'] ? $search_result['payments']->appends($search_condition_input_data)->links() : null }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    {{-- 新規登録ボタン --}}
                    @component('components.index.create_button')
                        @slot('route', route('trading.payments.create'))
                    @endcomponent
                </div>
            </div>
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th scope="col" style="width: 2%;">
                            <a class="centralOpen">+</a>
                            <a class="centralClose float-none" style="display: none;">-</a>
                        </th>
                        <th scope="col" class="col-md-1">伝票番号</th>
                        <th scope="col" class="col-md-1">伝票種別</th>
                        <th scope="col" class="col-md-1">伝票日付</th>
                        <th scope="col" class="col-md-2">仕入先名</th>
                        <th scope="col" class="col-md-1">部門</th>
                        <th scope="col" class="col-md-1">事業所</th>
                        <th scope="col" class="col-md-1">支払合計額</th>
                        <th scope="col" class="col-md-1">支払額</th>
                        <th scope="col" class="col-md-1">調整額</th>
                        <th scope="col" class="col-md-2">備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['payments'] as $order)
                        <tr>
                            <td class="text-center align-middle" data-title="詳細">
                                <a class="open">+</a>
                                <a class="close float-none" style="display: none;">-</a>
                            </td>
                            <td class="text-center align-middle" data-title="伝票番号">
                                @if ( $order->closing_at != null )
                                    <div class="btn-success">締処理済</div>
                                @endif
                                <a href="{{ route('trading.payments.edit', $order->id) }}">
                                    {{ $order['order_number_zero_fill'] }}
                                </a>
                            </td>
                            <td class="text-center align-middle" data-title="種別">
                                {{ TransactionType::getDescription($order->transaction_type_id) }}
                            </td>
                            <td class="text-center align-middle" data-title="伝票日付">
                                {{ $order->order_date_slash }}
                            </td>
                            <td class="text-left align-middle" data-title="仕入先名">
                                {{ $order->mSupplier->name }}
                            </td>
                            <td class="text-left align-middle" data-title="部門">
                                {{ $order->mDepartment->name }}
                            </td>
                            <td class="text-left align-middle" data-title="事業所">
                                {{ $order->mOfficeFacilities->name }}
                            </td>
                            <td class="text-right align-middle" data-title="支払合計額">
                                {{ $order->payment_comma ?? null }}
                            </td>
                            <td class="text-right align-middle" data-title="支払額">
                                {{ $order->paymentDetail->payment_comma ?? null }}
                            </td>
                            <td class="text-right align-middle" data-title="調整額">
                                {{ $order->paymentDetail->adjust_comma ?? null }}
                            </td>
                            <td class="text-left align-middle" data-title="備考">
                                {{ $order->note }}
                            </td>
                        </tr>
                        <tr class="detail" style="display: none;">
                            <td colspan="11">
                                <div class="row col-md-12">
                                    <div class="col-md-6">
                                        <p class="mb-1">■支払額</p>
                                        <table class="table table-bordered table-responsive-org table-sm mb-1"
                                               id="order_products_table">
                                            <thead class="thead-light text-center">
                                            <tr class="d-none d-md-table-row">
                                                <th style="width: 30%;">種別</th>
                                                <th style="width: 20%;">金額</th>
                                                <th style="width: 50%;">備考</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="text-left" data-title="種別-支払">現金</td>
                                                <td class="text-right" data-title="支払">
                                                    {{ $order->paymentDetail->amount_cash_comma ?? null }}
                                                </td>
                                                <td class="text-left" data-title="備考-支払">
                                                    {{ $order->paymentDetail->note_cash ?? null }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left" data-title="種別-小切手">小切手</td>
                                                <td class="text-right" data-title="小切手">
                                                    {{ $order->paymentDetail->amount_check_comma ?? null }}
                                                </td>
                                                <td class="text-left" data-title="備考-小切手">
                                                    {{ $order->paymentDetail->note_check ?? null }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left" data-title="種別-振込">振込</td>
                                                <td class="text-right" data-title="振込">
                                                    {{ $order->paymentDetail->amount_transfer_comma ?? null }}
                                                </td>
                                                <td class="text-left" data-title="備考-振込">
                                                    {{ $order->paymentDetail->note_transfer ?? null }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left" data-title="種別-手形">
                                                    <div>手形</div>
                                                </td>
                                                <td class="text-right" data-title="手形">
                                                    {{$order->paymentDetail->amount_bill_comma ?? NULL}}
                                                </td>
                                                <td class="text-left" data-title="備考-手形">
                                                    {{$order->paymentDetail->note_bill ?? NULL}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left" data-title="種別-相殺">相殺</td>
                                                <td class="text-right" data-title="相殺">
                                                    {{$order->paymentDetail->amount_offset_comma ?? NULL}}
                                                </td>
                                                <td class="text-left" data-title="備考-相殺">
                                                    {{$order->paymentDetail->note_offset ?? NULL}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" data-title="種別-支払額-小計">小計</td>
                                                <td class="text-right" data-title="支払額-小計">
                                                    {{$order->paymentDetail->payment_comma ?? NULL}}
                                                </td>
                                                <td class="text-left" data-title="支払額-小計">&nbsp;</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">■調整額</p>
                                        <table class="table table-bordered table-responsive-org table-sm mb-1"
                                               id="order_products_table">
                                            <thead class="thead-light text-center">
                                            <tr class="d-none d-md-table-row">
                                                <th style="width: 30%;">種別</th>
                                                <th style="width: 20%;">金額</th>
                                                <th style="width: 50%;">備考</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="text-left" data-title="種別-値引">値引</td>
                                                <td class="text-right" data-title="値引">
                                                    {{ $order->paymentDetail->amount_discount_comma ?? null }}
                                                </td>
                                                <td class="text-left" data-title="備考-値引">
                                                    {{ $order->paymentDetail->note_discount ?? null }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left" data-title="種別-手数料">手数料</td>
                                                <td class="text-right" data-title="手数料">
                                                    {{ $order->paymentDetail->amount_fee_comma ?? null }}
                                                </td>
                                                <td class="text-left" data-title="備考-手数料">
                                                    {{ $order->paymentDetail->note_fee ?? null }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left" data-title="種別-その他">その他</td>
                                                <td class="text-right" data-title="その他">
                                                    {{ $order->paymentDetail->amount_other_comma ?? null }}
                                                </td>
                                                <td class="text-left" data-title="備考-その他">
                                                    {{ $order->paymentDetail->note_other ?? null }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" data-title="種別-調整額-小計">小計</td>
                                                <td class="text-right" data-title="調整額-小計">
                                                    {{ $order->paymentDetail->adjust_comma ?? null }}
                                                </td>
                                                <td class="text-left" data-title="調整額-小計">&nbsp;</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['payments'] ? $search_result['payments']->appends($search_condition_input_data)->links() : null }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/trading/index.js') }}"></script>
    <script src="{{ mix('js/app/trading/payments/index.js') }}"></script>
@endsection
