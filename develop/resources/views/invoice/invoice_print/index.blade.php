{{-- 請求書発行画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.charge.menu.invoice_print');
    $next_url = route('invoice.invoice_print.index');
    $method = 'GET';
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
                {{-- 担当者 --}}
                @include('common.index.employee_select_list')
            </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>
        </form>

        <div class="col-md-12 pb-2 border-top pl-0">
            <div class="download-area mt-2">
                <div class="col-md-10 d-inline-flex">
                    {{-- 発行日 --}}
                    <label class="col-md-1.5 col-form-label">
                        <b>発行日</b>
                    </label>
                    <div class="col-md-3">

                        <input type="date" name="issue_date" id="issue_date" value="{{ $search_condition_input_data['issue_date'] ?? '' }}" class="form-control input-issue-date {{ $errors->has('issue_date.*') ? 'is-invalid' : '' }}" max="{{ config('consts.default.common.default_max_date') }}">

                    </div>
                    @error('issue_date.*')
                    <div class="invalid-feedback ml-3">{{ $message }}</div>
                    @enderror
                    <button type="button" id="printInvoicePdf"
                            class="btn btn-danger mr-1"
                            data-toggle="modal" data-target="#confirm-print-pdf"
                            @if ($search_result['charge_data']->isEmpty()) disabled @endif>
                        <i class="fas fa-file-pdf"></i>
                        PDF
                    </button>
                </div>

            </div>
        </div>

        <div class="col-md-12">
            <div style="font-size: 1.5em;">
                <label>
                    ■{{$search_result['charge_closing_date_display']}}
                    (対象期間：{{$search_result['charge_date_start']->format('Y年m月d日')}}
                    ～{{$search_result['charge_date_end']->format('Y年m月d日')}})
                </label>
            </div>
        </div>
        <div class="col-md-12">
            <div class="custom--table-area table-responsive">
                <table class="custom-table table-bordered table-responsive-org custom-table-fixed" id="chargeListTable">
                    <thead class="custom-thead-light">
                    <tr class="text-center">
                        <th style="width: 2%; text-align: center;">

                            <input type="checkbox" name="all_check" id="all_check" value="" {{ $search_condition_input_data['all_check'] ?? true ? 'checked' : '' }} onclick="allCheck(this);">

                        </th>
                        <th style="width: 45%;">得意先名</th>
                        <th style="width: 15%;">売掛金額</th>
                        <th style="width: 15%;">消費税額</th>
                        <th style="width: 10%;">個別</th>
                    </tr>
                    </thead>
                    <tbody id="chargeListTableBody">
                    @foreach ($search_result['charge_data'] ?? [] as $key => $detail)
                        <tr>
                            {{-- 出力対象チェックボックス --}}
                            <td class="text-center">
                                <div class="custom-form-check">

                                     <input type="checkbox" name="target_print" id="target_print" value="{{ $detail['id'] }}" {{ $search_condition_input_data[$key]['target_print'] ?? true ? 'checked' : '' }} class="form-check-input input-target-print {{ $errors->has('target_print') ? 'is-invalid' : '' }}">

                                </div>
                            </td>
                            <td style="display: none;">
                                {{$detail->id}}
                            </td>
                            {{-- 得意先名 --}}
                            <td class="text-left" data-title="得意先名" id="colCustomerId">
                                <a href="{{ route('invoice.charge_detail.index',
                                                        [
                                                            'customer_id' => $detail['customer_id'],
                                                            'charge_date' => $search_condition_input_data['charge_date'] ?? '',
                                                            'closing_date' => $search_condition_input_data['closing_date'] ?? '',
                                                        ]) }}">
                                    {{ StringHelper::getNameWithId($detail['customer_code_zerofill'], $detail['customer_name']) }}
                                </a>
                            </td>
                            {{-- 売掛金額 --}}
                            <td class="text-right" data-title="売掛金額">{{ number_format($detail['sales_total']) }}</td>
                            {{-- 消費税額 --}}
                            <td class="text-right"
                                data-title="消費税額">{{ number_format($detail['sales_tax_total']) }}</td>
                            <td class="text-center" data-title="帳票">
                                <button type="button" name="single-print" class="btn btn-primary btn-xs"
                                        onclick="printInvoicePdfSingle('{{$detail->id}}',
                                            '{{$detail->customer_id}}','{{$detail->customer_name}}');">
                                    請求書
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Excelボタン --}}
    <form name="downloadForm" id="downloadForm" action="{{ route('invoice.invoice_print.download_excel') }}">
        <input type="hidden" name="charge_data_ids" value="">
        <input type="hidden" name="issue_date" value="">
    @method('GET')
    </form>
    <form name="showPdfForm" id="showPdfForm" action="{{ route('invoice.invoice_print.show_pdf') }}" target="_blank" rel="noopener noreferrer">
        <input type="hidden" name="charge_data_ids" value="">
        <input type="hidden" name="customer_ids" value="">
        <input type="hidden" name="customer_names" value="">
        <input type="hidden" name="issue_date" value="">
    @method('GET')
    </form>

    {{-- Confirm Print Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-print')
        @slot('confirm_message', config('consts.message.common.confirm.excel'))
        @slot('onclick_btn_ok', "printInvoice();")
    @endcomponent
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-print-pdf')
        @slot('confirm_message', config('consts.message.common.confirm.pdf'))
        @slot('onclick_btn_ok', "printInvoicePdf();")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/invoice/index.js') }}"></script>
    <script src="{{ mix('js/app/invoice/invoice_print/index.js') }}"></script>
@endsection
