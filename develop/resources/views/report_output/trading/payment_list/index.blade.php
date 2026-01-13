{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar', ['active' => '/report_output/trading/payment_list'])
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.report_output.menu.payment_list');
    $next_url = route('report_output.trading.payment_list.index');
    $method = 'GET';
    $download_excel = route('report_output.trading.payment_list.download_excel');
    $download_pdf = route('report_output.trading.payment_list.download_pdf');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div id="loading" style="display: none;">
        <div class='loadingMsg'>処理中...</div>
    </div>

    <div class="row">
        <form name="searchForm" action="{{ $next_url }}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-body">

                </div>

                <div class="card-footer">
                    {{-- 帳票出力ボタン --}}
                    @include('common.index.report_output_button')
                </div>
            </div>

        </form>
    </div>

    <form name="downloadForm" id="download_form" action="" method="GET">
        @csrf
    </form>

    <script>
        /**
         * Excel ダウンロード
         *
         * @param url
         */
        function downloadExcel(url) {
            'use strict';
            document.getElementById('download_form').setAttribute('action', url);
            document.getElementById('download_form').submit();
        }

        /**
         * PDF ダウンロード
         *
         * @param url
         */
        function downloadPdf(url) {
            startPreloader();

            let datalist = {

            };

            downloadPdfAjax(url, datalist);
        }
    </script>
@endsection
