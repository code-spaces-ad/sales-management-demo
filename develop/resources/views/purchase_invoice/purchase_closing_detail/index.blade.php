{{-- 仕入締明細一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.trading.menu.closing.detail');
@endphp
@php
    use \App\Enums\PaymentMethodType;
@endphp
@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <div class="col-md-12 mb-1">
            <label class="col-md-9 col-form-label" style="font-size: 1.5em">
                {{ StringHelper::getNameWithId($search_result['purchase_closing_data']->mSupplier->code_zero_fill,
                    $search_result['purchase_closing_data']->mSupplier->name) }}
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
                    <th>前回締残</th>
                    <th>今回支払額</th>
                    <th>繰越金額</th>
                    <th>今回仕入額</th>
                    <th>消費税等</th>
                    <th>今回締残</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th class="text-center align-middle pc-no-display">合計行</th>
                    <td class="text-right" data-title="前回仕入締額">
                        {{ number_format($search_result['purchase_closing_data']->before_purchase_total) }}
                    </td>
                    <td class="text-right" data-title="今回支払額">
                        {{ number_format($search_result['purchase_closing_data']->payment_total) }}
                    </td>
                    <td class="text-right" data-title="繰越金額">
                        {{ number_format($search_result['purchase_closing_data']->carryover) }}
                    </td>
                    <td class="text-right" data-title="今回売上額">
                        {{ number_format($search_result['purchase_closing_data']->purchase_total) }}
                    </td>
                    <td class="text-right" data-title="消費税等">
                        {{ number_format($search_result['purchase_closing_data']->purchase_tax_total) }}
                    </td>
                    <td class="text-right" data-title="今回総仕入額">
                        {{ number_format($search_result['purchase_closing_data']->purchase_closing_total) }}
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
                        <th style="width: 2%;">種別</th>
                        <th style="width: 3%;">商品<br>コード</th>
                        <th style="width: 10%;">商品</th>
                        <th style="width: 6%;">数量</th>
                        <th style="width: 2%;">単位</th>
                        <th style="width: 6%;">単価</th>
                        <th style="width: 6%;">仕入</th>
                        <th style="width: 6%;">支払</th>
                        <th style="width: 6%;">備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (isset($search_result['order_details']))
                        @foreach ($search_result['order_details'] as $key => $order_detail)
                            @if ($order_detail->order_type == OrderType::PAYMENT)
                                {{--($order_detail['order_type'] == OrderType::DEPOSIT)--}}
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
                                        <th class="text-center align-middle pc-no-display">明細No.{{ $key + 1 }}</th>
                                        <td class="text-center"
                                            data-title="伝票日付">{{ $order_detail['order_date_slash'] }}</td>
                                        <td class="text-center" data-title="伝票番号">
                                            @php
                                                // 支払伝票へのリンク
                                                $order_link = route('trading.payments.edit', $order_detail['order_id']);
                                            @endphp
                                            <a href="{{ $order_link }}">{{ $order_detail['order_number'] }}</a>
                                        </td>
                                        <td class="text-center" data-title="伝票種別">
                                            @php
                                                $order_type_mark = '支';
                                            @endphp
                                            {{ $order_type_mark }}
                                        </td>
                                        <td class="text-center"
                                            data-title="商品コード">{{ $order_detail['product_code'] }}</td>
                                        <td class="text-left" data-title="商品">
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
                                        <td class="text-right" data-title="仕入">
                                            {{ ($order_detail['sub_total'] != '') ? number_format($order_detail['sub_total']) : '' }}
                                        </td>
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
                                                    $order_link = route('trading.purchase_orders.edit', $order_detail['order_id']);
                                        @endphp
                                        <a href="{{ $order_link }}">{{ $order_detail['order_number'] }}</a>
                                    </td>
                                    <td class="text-center" data-title="伝票種別">
                                        @php
                                            $order_type_mark = '仕';
                                        @endphp
                                        {{ $order_type_mark }}
                                    </td>
                                    <td class="text-center" data-title="商品コード">{{ $order_detail['product_code'] }}</td>
                                    <td class="text-left" data-title="商品">
                                        {{ $order_detail['product_name'] }}
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
                                    <td class="text-right" data-title="仕入">
                                        {{ ($order_detail['sub_total'] != '') ? number_format($order_detail['sub_total']) : '' }}
                                    </td>
                                    <td class="text-right" data-title="支払">
                                        {{ ($order_detail['payment_total'] != '') ? number_format($order_detail['payment_total']) : '' }}
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
                   href="{{ session('purchase_invoice_url', route('purchase_invoice.purchase_closing_list.index')) }}">
                    一覧画面へ戻る
                </a>
            </div>
        </div>
    </div>

    {{-- Search Supplier Modal --}}
    @component('components.search_supplier_modal')
        @slot('modal_id', 'search-supplier')
        @slot('suppliers', $search_items['suppliers'])
        @slot('onclick_select_supplier', "selectSupplierSearchSupplierModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/purchase_invoice/index.js') }}"></script>
    <script src="{{ mix('js/app/purchase_invoice/purchase_closing_detail/index.js') }}"></script>
@endsection
