{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar', ['active' => '/report_output/sale/summary_sales_by_customer_product_day'])
@extends('layouts.header')
@extends('layouts.footer')

@section('title', $view_settings['headline'] . ' | ' . config('app.name'))
@section('headline', $view_settings['headline'])

@section('content')
    <div id="loading" style="display: none;">
        <div class='loadingMsg'>処理中...</div>
    </div>

    <div class="row">
        <form name="searchForm" id="searchForm" action="{{ $view_settings['next_url'] }}" method="GET" class="col-12 px-0 px-md-4 pb-md-2">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-body">
                    {{-- 得意先範囲 --}}
                    @include('common.index.customer_range', ['required_customer' => true])

                    {{-- 商品範囲 --}}
                    @include('common.index.product_range', ['required_product' => true])

                    {{-- 売上日 --}}
                    @include('common.index.sales_date')
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

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/sale/index.js') }}"></script>
@endsection
