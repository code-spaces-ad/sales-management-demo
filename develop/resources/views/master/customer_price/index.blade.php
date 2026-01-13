{{-- 得意先別単価マスター一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.customer_price.index');
    $next_url = route('master.customer_price.index');
    $method = 'GET';
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')

        <form name="searchForm" action="{{ $next_url }}" method="{{ $next_url }}" class="col-12 px-0 px-md-4 pb-md-2">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- コード --}}
                    @include('common.master.index.code')

                    {{-- 得意先名 --}}
                    @include('common.index.customer_select_list')

                    {{-- 商品名 --}}
                    @include('common.master.index.product_name')

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
                    <input type="hidden" name="code[start]" value="{{ old('code.start', $search_condition_input_data['code']['start'] ?? '') }}">
                    <input type="hidden" name="code[end]" value="{{ old('code.end', $search_condition_input_data['code']['end'] ?? '') }}">
                    <input type="hidden" name="customer_id" value="{{ $search_condition_input_data['customer_id'] ?? ''}}">
                    <input type="hidden" name="customer_id_code" value="{{ $search_condition_input_data['customer_id_code'] ?? '' }}">
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}">
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success"
                   onclick="downloadPost('{{ route('master.customer_price.download_excel')}}');">
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
            <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                <div class="form-group d-md-inline-flex col-md-6 p-0 m-0">
                    {{ $search_result['customer_price']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    <a class="btn btn-primary" href="{{ route('master.customer_price.create') }}">
                        <i class="far fa-plus-square"></i>
                        新規登録
                    </a>
                </div>

            </div>

            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th scope="col"></th>
                        <th scope="col" class="col-md-1">コード</th>
                        <th scope="col" class="col-md-2">得意先名</th>
                        <th scope="col" class="col-md-2">商品名</th>
                        <th scope="col" class="col-md-2">通常税率_税込単価</th>
                        <th scope="col" class="col-md-2">軽減税率_税込単価</th>
                        <th scope="col" class="col-md-1">税抜単価</th>
                        <th scope="col" class="col-md-2">備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['customer_price'] ?? [] as $key => $customer_price)
                        <tr>
                            <th scope="row" class="text-center align-middle" onclick="checkingClickedOrNot('master.customer_price');"
                                data-title="編集">
                                <a href="{{ route('master.customer_price.edit', $customer_price->id) }}">
                                    <label class="btn btn-outline-info m-0">編集</label>
                                </a>
                            </th>
                            <td class="text-center align-middle" data-title="">
                                <div>{{ $customer_price->code_zerofill }}</div>
                            </td>
                            <td class="text-left align-middle" data-title="">
                                {{ $customer_price->mCustomer->name ?? '' }}
                            </td>
                            <td class="text-center align-middle" data-title="商品名">
                                {{ $customer_price->mProduct->name ?? '' }}
                            </td>
                            <td class="text-right align-middle" data-title="通常税率_税込単価">
                                {{ number_format($customer_price->tax_included, 2) }}
                            </td>
                            <td class="text-right align-middle" data-title="軽減税率_税込単価">
                                {{ number_format($customer_price->reduced_tax_included, 2) }}
                            </td>
                            <td class="text-right align-middle" data-title="税抜単価">
                                {{ number_format($customer_price->unit_price, 2) }}
                            </td>
                            <td class="text-center align-middle" data-title="備考">
                                {{ $customer_price->note }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['customer_price']->appends($search_condition_input_data)->links() }}
        </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/index.js') }}"></script>
    <script src="{{ mix('js/app/master/customer_price/index.js') }}"></script>
@endsection
