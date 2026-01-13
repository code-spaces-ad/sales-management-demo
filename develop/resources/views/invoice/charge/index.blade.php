{{-- 請求一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.charge.menu.index');
    $next_url = route('invoice.charge.index');
    $method = 'GET';
    /** @see MasterCustomersConst */
    $maxlength_customer_code = MasterCustomersConst::CODE_MAX_LENGTH;   // 得意先コード最大桁数
    // デフォルトMAX日付
    $default_max_date = config('consts.default.common.default_max_date');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <form name="searchForm" id="searchForm" action="{{ $next_url }}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- 締年月日 --}}
                    @include('common.index.charge_closing_date')

                    {{-- 請求先 --}}
                    @include('common.index.customer_select_list')

                    @php
                        $required_department = true;
                        $required_office_facility = true;
                    @endphp
                    {{-- 部門 --}}
                    @include('common.index.department_select_list')
                    {{-- 事業所 --}}
                    @include('common.index.office_facility_select_list')
                </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>
        </form>

        <div class="col-md-12 d-flex justify-content-between mb-2">
            <div class="download-area">
                <form name="downloadForm" id="download_form" action="" method="GET">
                @csrf
                    <input type="hidden" name="charge_date" value="{{ $search_condition_input_data['charge_date'] ?? "" }}">
                    <input type="hidden" name="closing_date" value="{{ $search_condition_input_data['closing_date'] ?? "" }}">
                    <input type="hidden" name="customer_id" value="{{ $search_condition_input_data['customer_id'] ?? "" }}">
                    <input type="hidden" name="department_id" value="{{ $search_condition_input_data['department_id'] ?? "" }}">
                    <input type="hidden" name="office_facility_id" value="{{ $search_condition_input_data['office_facility_id'] ?? "" }}">
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success{{ $search_result['charge_data']->isEmpty() ? ' disabled' : '' }}"
                   @if($search_result['charge_data']->isEmpty())
                       style="pointer-events: none;"
                   @else
                       onclick="downloadPost('{{ route('invoice.charge.download_excel') }}')"
                   @endif>
                    <i class="fas fa-file-excel"></i>
                    Excel
                </a>

                <script>
                    function downloadPost(url) {
                        'use strict';

                        document.getElementById('download_form').setAttribute('action', url);
                        document.getElementById('download_form').submit();
                    }
                </script>
            </div>
        </div>

        <div class="col-md-12">
            {{ $search_result['charge_data']->appends($search_condition_input_data)->links() }}
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <td colspan="1" class="border-0" style="background-color: transparent;"></td>
                        <th style="width: 7%;font-size: 0.75rem;">前回請求額</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回入金額</th>
                        <th style="width: 7%;font-size: 0.75rem;">調整額</th>
                        <th style="width: 7%;font-size: 0.75rem;">繰越残高</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回売上額</th>
                        <th style="width: 6%;font-size: 0.75rem;">消費税</th>
                        <th style="width: 8%;font-size: 0.75rem;">今回総売上額</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回請求額</th>
                        <td colspan="1" class="border-0" style="background-color: transparent;"></td>
                    </tr>
                    <tr>
                        <td colspan="1" class="border-0" style="background-color: transparent;"></td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="前回請求額">
                            {{ number_format($search_result['charge_data_total']['before_charge_total']) }}
                        </td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="今回入金額">
                            {{ number_format($search_result['charge_data_total']['payment_total']) }}
                        </td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="調整額">
                            {{ number_format($search_result['charge_data_total']['adjust_amount_total']) }}
                        </td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="繰越残高">
                            {{ number_format($search_result['charge_data_total']['carryover_total']) }}
                        </td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="今回売上額">
                            {{ number_format($search_result['charge_data_total']['sales_total']) }}
                        </td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="消費税">
                            {{ number_format($search_result['charge_data_total']['sales_tax_total']) }}
                        </td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="今回総売上額">
                            {{ number_format($search_result['charge_data_total']['sales_total_amount_total']) }}
                        </td>
                        <td class="text-right border-width"
                            style="background-color: transparent;" data-title="今回請求額">
                            {{ number_format($search_result['charge_data_total']['charge_total']) }}
                        </td>
                        <td colspan="1" class="border-0" style="background-color: transparent;"></td>
                    </tr>
                    <tr>
                        <td class="border-0" style="height: 10px"></td>
                    </tr>
                    <tr class="text-center">
                        <th style="width: 20%;font-size: 0.75rem;">請求先</th>
                        <th style="width: 7%;font-size: 0.75rem;">前回請求額</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回入金額</th>
                        <th style="width: 7%;font-size: 0.75rem;">調整額</th>
                        <th style="width: 7%;font-size: 0.75rem;">繰越残高</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回売上額</th>
                        <th style="width: 7%;font-size: 0.75rem; display: none;">値引・調整</th>
                        <th style="width: 6%;font-size: 0.75rem;">消費税</th>
                        <th style="width: 8%;font-size: 0.75rem;">今回総売上額</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回請求額</th>
                        <th style="width: 10%;font-size: 0.75rem;">入金予定日</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['charge_data'] ?? [] as $detail)
                        @if (!$detail['customer_name'])
                            @continue
                        @endif
                        <tr>
                            <th class="text-center align-middle pc-no-display"></th>
                            <td class="text-left" data-title="請求先">
                                <a href="{{ route('invoice.charge_detail.index',
                                    [
                                        'customer_id' => $detail['customer_id'],
                                        'charge_date' => $search_condition_input_data['charge_date'] ?? '',
                                        'closing_date' => $search_condition_input_data['closing_date'] ?? '',
                                    ]) }}">
                                    {{ StringHelper::getNameWithId($detail['customer_code_zerofill'], $detail['customer_name']) }}
                                </a>
                            </td>
                            <td class="text-right"
                                data-title="前回請求額">
                                {{ number_format($detail['before_charge_total']) }}
                            </td>
                            <td class="text-right"
                                data-title="今回入金額">
                                {{ number_format($detail['payment_total']) }}
                            </td>
                            <td class="text-right"
                                data-title="調整額">
                                {{ number_format($detail['adjust_amount']) }}
                            </td>
                            <td class="text-right" data-title="繰越残高">
                                {{ number_format($detail['carryover']) }}
                            </td>
                            <td class="text-right"
                                data-title="今回売上額">
                                {{ number_format($detail['sales_total']) }}
                            </td>
                            <td class="text-right" data-title="値引・調整"
                                style="display: none;">
                                {{ number_format($detail['discount_total']) }}
                            </td>
                            <td class="text-right"
                                data-title="消費税">
                                {{ number_format($detail['sales_tax_total']) }}
                            </td>
                            <td class="text-right"
                                data-title="今回総売上額">
                                {{ number_format($detail['sales_total_amount']) }}
                            </td>
                            <td class="text-right"
                                data-title="今回請求額">
                                {{ number_format($detail['charge_total']) }}
                            </td>
                            <td class="text-left" data-title="入金予定日">
                                {{ $detail->planned_deposit_at_slash }}
                                {{ DepositMethodType::getDescription($detail->collection_method) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['charge_data']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/invoice/index.js') }}"></script>
    <script src="{{ mix('js/app/invoice/charge/index.js') }}"></script>
@endsection
