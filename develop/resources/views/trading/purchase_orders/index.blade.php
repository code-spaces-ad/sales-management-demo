{{-- 仕入伝票一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.trading.menu.index');
    $next_url = route('trading.purchase_orders.index');
    $method = 'GET';
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

                {{-- 仕入日 --}}
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
                    <th class="col-md-1">仕入合計</th>
                    <th class="border-0 col-md-2" style="background-color: transparent;"></th>
                </tr>
                <tr style="background-color: transparent;">
                    <td class="border-0" style="background-color: transparent;"></td>
                    <td class="border-0" style="background-color: transparent;"></td>
                    <td class="text-right align-middle border-width"
                        style="background-color: transparent;" data-title="仕入合計">
                        {{ number_format(($search_result['purchase_orders_total']['purchase_total'] ?? 0) - ($search_result['purchase_orders_total']['discount'] ?? 0 )) }}
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
                    {{ $search_result['purchase_orders']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    {{-- 新規登録ボタン --}}
                    @component('components.index.create_button')
                        @slot('route', route('trading.purchase_orders.create'))
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
                        <th scope="col" class="col-md-1">仕入日</th>
                        <th scope="col" class="col-md-2">仕入先名</th>
                        <th scope="col" class="col-md-1">部門</th>
                        <th scope="col" class="col-md-1">事業所</th>
                        <th scope="col" class="col-md-1">金額計(小計)</th>
                        <th scope="col" class="col-md-1">消費税</th>
                        <th scope="col" class="col-md-1">更新者</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['purchase_orders'] ?? [] as $key => $purchase_order)
                        <tr>
                            <td class="text-center" data-title="詳細">
                                <a class="open">+</a>
                                <a class="close float-none" style="display: none;">-</a>
                            </td>
                            <th class="text-center align-middle pc-no-display"></th>
                            {{-- 伝票番号 --}}
                            <td class="text-center" data-title="伝票番号">
                                @if ( $purchase_order->closing_at != null )
                                    <div class="btn-success">締処理済</div>
                                @endif
                                @if ( $purchase_order->link_pos === \App\Enums\LinkPos::POS )
                                    <div class="btn-warning">POS</div>
                                @endif
                                <a href="{{ route('trading.purchase_orders.edit', $purchase_order->id) }}">
                                    {{ $purchase_order->order_number_zerofill }}
                                </a>
                            </td>
                            {{-- 伝票種別 --}}
                            <td class="text-center" data-title="伝票種別">
                                {{ TransactionType::asselectArray()[$purchase_order->transaction_type_id] }}
                            </td>
                            {{-- 仕入日 --}}
                            <td class="text-center" data-title="仕入日">
                                {{ $purchase_order->order_date_slash }}
                            </td>
                            {{-- 仕入先名 --}}
                            <td class="text-left" data-title="仕入先名">
                                {{ $purchase_order->supplier_name }}
                            </td>
                            {{-- 部門 --}}
                            <td class="text-left" data-title="部門">
                                {{ $purchase_order->department_name }}
                            </td>
                            {{-- 事業所 --}}
                            <td class="text-left" data-title="事業所">
                                {{ $purchase_order->office_facilities_name }}
                            </td>
                            {{-- 金額 --}}
                            <td class="text-right" data-title="金額">
                                {{ number_format(($purchase_order->purchase_total ?? 0) - ($purchase_order->discount ?? 0)) }}
                            </td>
                            <td class="text-right" data-title="消費税">
                                @if( $purchase_order->tax_calc_type_id === \App\Enums\TaxCalcType::ORDER ||
                                    $purchase_order->tax_calc_type_id === \App\Enums\TaxCalcType::DETAIL)
                                    {{ number_format($purchase_order->tax ?? 0) }}
                                @endif
                                @if( $purchase_order->tax_calc_type_id === \App\Enums\TaxCalcType::BILLING )
                                    <b>※請求計算</b>
                                @endif
                                @if( $purchase_order->tax_calc_type_id === \App\Enums\TaxCalcType::NONE )
                                    <b>※無処理</b>
                                @endif
                            </td>
                            {{-- 更新者 --}}
                            <td class="text-left" data-title="更新者">
                                {{ $purchase_order->updated_name }}
                            </td>
                        </tr>
                        <tr class="detail" style="display: none;">
                            <td colspan="10">
                                <table class="table table-fixed mb-1 table-th-white"
                                       id="order_products_table"
                                       style="max-height: none !important;">
                                    <thead class="thead-light text-center border-md-silver">
                                    <tr class="d-none d-md-table-row">
                                        <th style="width: 3%;">No.</th>
                                        <th style="width: 36%;">商品</th>
                                        <th style="width: 7%;">数量</th>
                                        <th style="width: 7%;">単価</th>
                                        <th style="width: 7%;">小計</th>
                                        <th style="width: 5%;">値引額</th>
                                        <th style="width: 10%;">金額</th>
                                        <th style="width: 13%;">備考</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($purchase_order->purchaseOrderDetail as $key => $detail)
                                        <tr>
                                            <th class="text-center align-middle text-dark border-md-none">
                                                {{ ++$key }}
                                            </th>
                                            <td class="text-left align-middle border-top-0 border-md-none"
                                                data-title="商品">
                                                {{ $detail->product_name }}
                                            </td>
                                            <td class="text-right align-middle border-top-0 border-md-none"
                                                data-title="数量">
                                                {{ number_format($detail->quantity) }}
                                            </td>
                                            <td class="text-right align-middle border-top-0 border-md-none"
                                                data-title="単価">
                                                {{ number_format($detail->unit_price) }}
                                            </td>
                                            <td class="text-right align-middle border-top-0 border-md-none"
                                                data-title="小計">
                                                {{ number_format($detail->unit_price * $detail->quantity) }}
                                            </td>
                                            <td class="text-right align-middle border-top-0 border-md-none"
                                                @if(  number_format($detail->discount) > 0 ) style="color: red" @endif
                                                data-title="値引額">
                                                {{ number_format($detail->discount) }}
                                            </td>
                                            <td class="text-right align-middle border-top-0 border-md-none"
                                                data-title="金額">
                                                {{ number_format(($detail->unit_price * $detail->quantity) - $detail->discount) }}
                                            </td>
                                            <td class="text-left align-middle border-top-0 border-md-none"
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
            {{ $search_result['purchase_orders']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/trading/index.js') }}"></script>
    <script src="{{ mix('js/app/trading/purchase_orders/index.js') }}"></script>
@endsection
