{{-- 年度別販売実績表画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.sale.menu.sale_ledger_submenu.fiscal_year');
    $next_url = route('sale.ledger.fiscal_year');
    $excel_download_url = route('sale.ledger.fiscal_year.download_excel');
    $show_pdf_url = route('sale.ledger.fiscal_year.show_pdf');
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
                    {{-- 会計年度 --}}
                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        <label class="col-md-2 col-form-label pl-0 pb-md-3">
                            <b>会計年度</b>
                        </label>
                        <div class="d-md-inline-flex col-md-10 pr-md-0">
                            <select name="fiscal_year"
                                    class="custom-select input-fiscal-year-select mr-md-1 clear-select
                                    @if($errors->has('fiscal_year')) is-invalid @endif">
                                <option value="">-----</option>
                                @foreach (($search_items['fiscal_year'] ?? []) as $key => $item)
                                    <option
                                        @if ($key == old('fiscal_year', $search_condition_input_data['fiscal_year'] ?? null))
                                            selected
                                        @endif
                                        value="{{ $key }}">
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fiscal_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 集計種別 --}}
                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        <label class="col-form-label col-md-2 pl-0 pb-md-3">
                            <b>集計種別</b>
                        </label>
                        <div class="d-md-inline-flex col-md-9 pr-md-0">
                            @foreach (($search_items['aggregation_types'] ?? []) as $key => $val)
                                <div class="icheck-primary icheck-inline mr-2">

                                    <input type="radio" name="aggregation_type" value="{{ $key }}" id="aggregation-type-{{ $key }}"
                                        {{ $key === $search_condition_input_data['aggregation_type'] ? 'checked' : '' }}>

                                    <label class="form-check-label"
                                        for="aggregation-type-{{ $key }}">{{ $val }}</label>
                                </div>
                            @endforeach
                            @error('aggregation_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
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
                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start', $search_condition_input_data['order_date']['start'] ?? ' ') }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end', $search_condition_input_data['order_date']['end'] ?? ' ') }}">
                            <input type="hidden" name="aggregation_type" value="{{ $search_condition_input_data['aggregation_type'] ?? 'sub_total' }}">

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
                            <input type="hidden" name="aggregation_type" value="{{ $search_condition_input_data['aggregation_type'] ?? 'sub_total' }}">

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
                        <th>年度合計</th>
                    </tr>
                    <tr style="background-color: transparent;">
                        <td colspan="1" class="border-0" style="background-color: transparent;"></td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="年度合計">
                            {{ number_format($search_result['fiscal_total'] ?? 0) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0" style="height: 10px"></td>
                    </tr>
                    <tr class="text-center">
                        <th scope="col">伝票月</th>
                        <th scope="col">月計</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['sales_orders'] as $order)
                        <tr>
                            <td class="text-center" data-title="伝票日付">
                                {{ $order['order_month'] }}
                            </td>
                            <td class="text-right" data-title="日計">
                                {{ number_format($order['month_total']) }}
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
    <script src="{{ mix('js/app/sale/ledger/fiscal_year/index.js') }}"></script>
@endsection
