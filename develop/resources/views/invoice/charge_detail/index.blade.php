{{-- 請求明細一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.charge.menu.detail');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <div class="col-md-12 mb-1">
            <label class="col-md-9 col-form-label" style="font-size: 1.5em">
                {{ StringHelper::getNameWithId($search_result['charge_data']->mCustomer->code_zero_fill, $search_result['charge_data']->mCustomer->name) }}
            </label>
            <label class="col-md-9 col-form-label" style="font-size: 1.5em">
                ■{{$search_result['charge_closing_date_display']}}
                (対象期間：{{$search_result['charge_date_start']->format('Y年m月d日')}}
                ～{{$search_result['charge_date_end']->format('Y年m月d日')}})
            </label>
        </div>

        <div class="col-md-10">
            <table class="table table-bordered table-responsive-org">
                <thead class="thead-light">
                <tr class="text-center">
                    <th>前回請求額</th>
                    <th>今回入金額</th>
                    <th>調整額</th>
                    <th>繰越金額</th>
                    <th>今回売上額</th>
                    <th>消費税等</th>
                    <th>今回総売上額</th>
                    <th>今回請求額</th>
                    <th>入金予定日</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th class="text-center align-middle pc-no-display">合計行</th>
                    <td class="text-right" data-title="前回請求額">
                        {{ number_format($search_result['charge_data']->before_charge_total) }}
                    </td>
                    <td class="text-right" data-title="今回入金額">
                        {{ number_format($search_result['charge_data']->payment_total) }}
                    </td>
                    <td class="text-right" data-title="調整額">
                        {{ number_format($search_result['charge_data']->adjust_amount) }}
                    </td>
                    <td class="text-right" data-title="繰越金額">
                        {{ number_format($search_result['charge_data']->carryover) }}
                    </td>
                    <td class="text-right" data-title="今回売上額">
                        {{ number_format($search_result['charge_data']->sales_total) }}
                    </td>
                    <td class="text-right" data-title="消費税等">
                        {{ number_format($search_result['charge_data']->sales_tax_total) }}
                    </td>
                    <td class="text-right" data-title="今回総売上額">
                        {{ number_format($search_result['charge_data']->sales_total_amount) }}
                    </td>
                    <td class="text-right" data-title="今回請求額">
                        {{ number_format($search_result['charge_data']->charge_total) }}
                    </td>
                    <td class="text-center" data-title="入金予定日">
                        {{ $search_result['charge_data']->planned_deposit_at_slash }}
                        {{ DepositMethodType::getDescription($search_result['charge_data']->collection_method) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-12">
            <div class="download-area">
                <form name="downloadForm" id="download_form" action="" method="GET">
                @csrf
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}">
                    <input type="hidden" name="name_kana" value="{{ $search_condition_input_data['name_kana'] ?? '' }}">
                </form>
            </div>
            <br>

            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th style="width: 3%;">伝票日付</th>
                        <th style="width: 3%;">伝票番号</th>
                        <th style="width: 3%;">種別</th>
                        <th style="width: 19%;">得意先名</th>
                        <th style="width: 11%;">支所名</th>
                        <th style="width: 5%;">商品<br>コード</th>
                        <th style="width: 26%;">商品</th>
                        <th style="width: 3%;">数量</th>
                        <th style="width: 3%;">単位</th>
                        <th style="width: 4%;">単価</th>
                        <th style="width: 5%;">売上</th>
                        <th style="width: 5%;">入金</th>
                        <th style="width: 12%;">備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (isset($search_result['order_details']))
                        @foreach ($search_result['order_details'] as $key => $order_detail)
                            @if($order_detail['order_type'] == OrderType::DEPOSIT)
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
                                        <th class="text-center align-middle pc-no-display">明細No.{{ $key + 1 }}</th>
                                        <td class="text-center"
                                            data-title="伝票日付">{{ $order_detail['order_date_slash'] }}</td>
                                        <td class="text-center" data-title="伝票番号">
                                            @php
                                                // 入金伝票へのリンク
                                                     $order_link = route('sale.deposits.edit', $order_detail['order_id']);
                                            @endphp
                                            <a href="{{ $order_link }}">{{ $order_detail['order_number'] }}</a>
                                        </td>
                                        <td class="text-center" data-title="伝票種別">
                                            @php
                                                $order_type_mark = '入';
                                            @endphp
                                            {{ $order_type_mark }}
                                        </td>
                                        <td class="text-left" data-title="得意先名">{{ $order_detail['name'] }}</td>
                                        <td class="text-left" data-title="支所名">{{ $order_detail['b_name'] }}</td>
                                        <td class="text-center"
                                            data-title="商品コード">{{ $order_detail['product_code'] }}</td>
                                        <td class="text-left" data-title="商品">
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
                                        <td class="text-right" data-title="数量">
                                            @if ($order_detail['quantity'] != null)
                                                {{ number_format($order_detail['quantity'], $order_detail['quantity_decimal_digit'] ?? 0) }}
                                            @endif
                                        </td>
                                        <td class="text-center" data-title="単位">{{ $order_detail['unit_name'] }}</td>
                                        <td class="text-right" data-title="単価">
                                            @if ($order_detail['unit_price'] != null)
                                                {{ number_format($order_detail['unit_price'], $order_detail['unit_price_decimal_digit'] ?? 0) }}
                                            @endif
                                        </td>
                                        <td class="text-right" data-title="売上">
                                            {{ ($order_detail['sub_total'] != '') ? number_format($order_detail['sub_total']) : '' }}
                                        </td>
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
                                        <td class="text-left" data-title="備考">{{ $order_detail['detail_note'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <th class="text-center align-middle pc-no-display">明細No.{{ $key + 1 }}</th>
                                    <td class="text-center"
                                        data-title="伝票日付">{{ $order_detail['order_date_slash'] }}</td>
                                    <td class="text-center" data-title="伝票番号">
                                        @php
                                            // 売上伝票へのリンク
                                                $order_link = route('sale.orders.edit', $order_detail['order_id']);
                                        @endphp
                                        <a href="{{ $order_link }}">{{ $order_detail['order_number'] }}</a>
                                    </td>
                                    <td class="text-center" data-title="伝票種別">
                                        @php
                                            $order_type_mark = '掛';
                                        @endphp
                                        {{ $order_type_mark }}
                                    </td>
                                    <td class="text-left" data-title="得意先名">{{ $order_detail['name'] }}</td>
                                    <td class="text-left" data-title="支所名">{{ $order_detail['b_name'] }}</td>
                                    <td class="text-center" data-title="商品コード">{{ $order_detail['product_code'] }}</td>
                                    <td class="text-left" data-title="商品">{{ $order_detail['product_name'] }}</td>
                                    <td class="text-right" data-title="数量">
                                        @if ($order_detail['quantity'] != null)
                                            {{ number_format($order_detail['quantity'], $order_detail['quantity_decimal_digit'] ?? 0) }}
                                        @endif
                                    </td>
                                    <td class="text-center" data-title="単位">{{ $order_detail['unit_name'] }}</td>
                                    <td class="text-right" data-title="単価">
                                        @if ($order_detail['unit_price'] != null)
                                            {{ number_format($order_detail['unit_price'], $order_detail['unit_price_decimal_digit'] ?? 0) }}
                                        @endif
                                    </td>
                                    <td class="text-right" data-title="売上">
                                        {{ ($order_detail['sub_total'] != '') ? number_format($order_detail['sub_total']) : '' }}
                                    </td>
                                    <td class="text-right" data-title="入金">
                                        {{ ($order_detail['deposit_total'] != '') ? number_format($order_detail['deposit_total']) : '' }}
                                    </td>
                                    <td class="text-left" data-title="備考">{{ $order_detail['detail_note'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="buttons-area text-center mt-2">
                <a id="return" class="btn btn-primary back_active"
                   href="{{ session('invoice_url', route('invoice.charge.index')) }}">
                    一覧画面へ戻る
                </a>
            </div>
        </div>
    </div>

    {{-- Search Custmoer Modal --}}
    @component('components.search_customer_modal')
        @slot('modal_id', 'search-customer')
        @slot('customers', $search_items['customers'])
        @slot('onclick_select_customer', "selectCustomerSearchCustomerModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/invoice/index.js') }}"></script>
    <script src="{{ mix('js/app/invoice/charge_detail/index.js') }}"></script>
@endsection
