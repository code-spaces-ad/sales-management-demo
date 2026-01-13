{{-- 仕入締一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.trading.menu.closing.index');
    $next_url = route('purchase_invoice.purchase_closing_list.index');
    $method = 'GET';
    /** @see MasterSuppliersConst */
    $maxlength_supplier_code = MasterSuppliersConst::CODE_MAX_LENGTH;   // 得意先コード最大桁数
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
                    @include('common.index.purchase_closing_date')

                    {{-- 仕入先 --}}
                    @include('common.index.supplier_select_list')

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
                    <input type="hidden" name="purchase_date" value="{{ $search_condition_input_data['purchase_date'] ?? "" }}">
                    <input type="hidden" name="closing_date" value="{{ $search_condition_input_data['closing_date'] ?? "" }}">
                    <input type="hidden" name="supplier_id" value="{{ $search_condition_input_data['supplier_id'] ?? "" }}">
                    <input type="hidden" name="department_id" value="{{ $search_condition_input_data['department_id'] ?? "" }}">
                    <input type="hidden" name="office_facility_id" value="{{ $search_condition_input_data['office_facility_id'] ?? "" }}">
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success{{ $search_result['purchase_closing_list_data']->isEmpty() ? ' disabled' : '' }}"
                   @if($search_result['purchase_closing_list_data']->isEmpty())
                       style="pointer-events: none;"
                   @else
                       onclick="downloadPost('{{ route('purchase_invoice.purchase_closing_list.download_excel') }}')"
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
            {{ $search_result['purchase_closing_list_data']->appends($search_condition_input_data)->links() }}
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">

                    <tr class="text-center">
                        <th colspan="1" class="border-0" style="background-color: transparent;"></th>
                        <th>前回仕入額合計</th>
                        <th>今回支払額合計</th>
                        <th>繰越残高合計</th>
                        <th>今回仕入額合計</th>
                        <th>消費税合計</th>
                        <th>今回総仕入額合計</th>
                    </tr>
                    <tr style="background-color: transparent;">
                        <td colspan="1" class="border-0" style="background-color: transparent;"></td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="前回仕入額合計">
                            {{ number_format($search_result['purchase_closing_total']['before_purchase_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="今回支払額合計">
                            {{ number_format($search_result['purchase_closing_total']['payment_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="繰越残高合計">
                            {{ number_format($search_result['purchase_closing_total']['carryover_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="今回仕入額合計">
                            {{ number_format($search_result['purchase_closing_total']['purchase_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="消費税合計">
                            {{ number_format($search_result['purchase_closing_total']['purchase_tax_total'] ?? 0) }}
                        </td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="今回総仕入額合計">
                            {{ number_format($search_result['purchase_closing_total']['purchase_closing_total'] ?? 0) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0" style="height: 10px"></td>
                    </tr>
                    <tr class="text-center">
                        <th style="width: 20%;font-size: 0.75rem;">仕入先</th>
                        <th style="width: 7%;font-size: 0.75rem;">前回仕入額</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回支払額</th>
                        <th style="width: 7%;font-size: 0.75rem;">繰越残高</th>
                        <th style="width: 7%;font-size: 0.75rem;">今回仕入額</th>
                        <th style="width: 7%;font-size: 0.75rem; display: none;">値引・調整</th>
                        <th style="width: 6%;font-size: 0.75rem;">消費税</th>
                        <th style="width: 8%;font-size: 0.75rem;">今回総仕入額</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['purchase_closing_list_data'] ?? [] as $detail)
                        @if (!$detail['supplier_name'])
                            @continue
                        @endif
                        <tr>
                            <th class="text-center align-middle pc-no-display"></th>
                            <td class="text-left" data-title="請求先">
                                <a href="{{ route('purchase_invoice.purchase_closing_detail.index',
                                             [
                                                 'supplier_id' => $detail['supplier_id'],
                                                 'purchase_date' => $search_condition_input_data['purchase_date'] ?? '',
                                                 'closing_date' => $search_condition_input_data['closing_date'] ?? '',
                                             ]) }}">
                                    {{ StringHelper::getNameWithId($detail['supplier_code_zerofill'], $detail['supplier_name']) }}
                                </a>
                            </td>
                            <td class="text-right"
                                data-title="前回請求額">{{ number_format($detail['before_purchase_total']) }}</td>
                            <td class="text-right"
                                data-title="今回入金額">{{ number_format($detail['payment_total']) }}</td>
                            <td class="text-right" data-title="繰越残高">{{ number_format($detail['carryover']) }}</td>
                            <td class="text-right"
                                data-title="今回売上額">{{ number_format($detail['purchase_total']) }}</td>
                            <td class="text-right" data-title="値引・調整"
                                style="display: none;">{{ number_format($detail['discount_total']) }}</td>
                            <td class="text-right"
                                data-title="消費税">{{ number_format($detail['purchase_tax_total']) }}</td>
                            <td class="text-right"
                                data-title="今回総売上額">{{ number_format($detail['purchase_closing_total']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['purchase_closing_list_data']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/purchase_invoice/index.js') }}"></script>
    <script src="{{ mix('js/app/purchase_invoice/purchase_closing_list/index.js') }}"></script>
@endsection
