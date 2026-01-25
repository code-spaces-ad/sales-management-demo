{{-- 仕入締処理画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.trading.menu.closing.closing');
    $next_url = route('purchase_invoice.purchase_closing.index');
    $method = 'GET';
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">

        <form name="searchForm" id="searchForm" action="{{ $next_url }}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">
            @csrf

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

        <div class="col-md-12 border-top">
            <div class="download-area">
                <form name="downloadForm" action="" id="download_form" method="POST">
                @method($method)
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? null }}">
                    <input type="hidden" name="name_kana" value="{{ $search_condition_input_data['name_kana'] ?? null }}">
                </form>
            </div>
            <br>

            <div style="font-size: 1.5em;">
                <label>
                    ■{{$search_result['purchase_closing_date_display']}}
                    (対象期間：{{$search_result['purchase_date_start']->format('Y年m月d日')}}
                    ～{{$search_result['purchase_date_end']->format('Y年m月d日')}})
                </label>
            </div>

            <div class="custom-table-area table-responsive">
                <table class="custom-table table-bordered table-responsive-org custom-table-fixed">
                    <thead class="custom-thead-light">
                    <tr class="text-center">
                        <th style="width: 2%; text-align: center;">

                            <input type="checkbox" name="all_check" id="all_check"
                                value="" {{ $search_condition_input_data['all_check'] ?? true ? 'checked' : '' }}
                                onclick="allCheck(this);">

                        </th>
                        <th style="width: 30%; text-align: center;">仕入先名</th>
                        <th style="width: 8%; text-align: center;">部門</th>
                        <th style="width: 8%; text-align: center;">事業所</th>
                        <th style="width: 8%; text-align: center;">実施者</th>
                        <th style="width: 12%; text-align: center;">締処理日時</th>
                        <th style="width: 5%; text-align: center;">仕入金額</th>
                        <th style="width: 5%; text-align: center;">消費税額</th>
                        <th style="width: 3%; text-align: center;">仕入</th>
                        <th style="width: 3%; text-align: center;">支払</th>
                        <th style="width: 5%; text-align: center;">個別</th>
                        <th style="width: 4%; text-align: center;display: none">帳票</th>
                    </tr>
                    </thead>
                    <tbody>
                    <x-purchase_invoice.billing_supplier_list :search_result="$search_result" :search_condition_input_data="$search_condition_input_data" />
                    </tbody>
                </table>
            </div>

            <div class="buttons-area text-center mt-2">
                <button type="button" id="bulk-closing" class="btn btn-primary pl-4 pr-4 pt-2 pb-2 mr-2"
                        data-toggle="modal" data-target="#confirm-store"
                        style="font-size: 1.5em;" disabled>
                    <i class="fas fa-calculator"></i>
                    <div class="spinner-border text-light" role="status" style="display: none;"></div>
                    締処理の実行
                </button>
                <button type="button" id="bulk-cancel" class="btn btn-danger pl-4 pr-4 pt-2 pb-2"
                        data-toggle="modal" data-target="#confirm-cancel"
                        style="font-size: 1.5em;">
                    <i class="fas fa-undo"></i>
                    <div class="spinner-border text-light" role="status" style="display: none;"></div>
                    締処理の解除
                </button>
                <b class="invalid-feedback">※「締処理の実行」メンテナンス中※</b>
            </div>
        </div>
    </div>

    <input type="hidden" name="purchase_closing_store" value="{{ route('purchase_invoice.purchase_closing.store') }}" >

    <form name="cancelForm" id="cancelForm" action="{{ route('purchase_invoice.purchase_closing.cancel') }}" method="POST">
    @csrf
        <input type="hidden" name="purchase_data_ids" value="">

        {{-- 登録ボタン --}}
        <input type="submit" id="btn_submit" style="display:none;" value="">
    </form>

    {{-- Confirm Store Modal --}}
    {{-- @component('components.confirm_modal')
        @slot('modal_id', 'confirm-store')
        @slot('confirm_message',
            config('consts.message.purchase_closing.confirm.store') . "\r\n※締未処理の仕入先が対象となります。"
        )
        @slot('onclick_btn_ok', "chargeClosingStore('" . route('api.purchase_invoice.closing_job') . "');return false;")
    @endcomponent --}}

    {{-- Confirm Cancel Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-cancel')
        @slot('confirm_message', config('consts.message.purchase_closing.confirm.cancel') . "\r\n※締処理済の仕入先が対象となります。" )
        @slot('onclick_btn_ok', "chargeClosingCancel();")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/purchase_invoice/index.js') }}"></script>
    <script src="{{ mix('js/app/purchase_invoice/purchase_closing/index.js') }}"></script>
@endsection
