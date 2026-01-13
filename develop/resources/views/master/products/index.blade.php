{{-- 商品マスター一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.products.index');
    $next_url = route('master.products.index');
    $method = 'GET';
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
                    {{-- コード --}}
                    @include('common.master.index.code')

                    {{-- 商品名 --}}
                    @include('common.master.index.product_name')

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- カテゴリー --}}
                        @include('common.index.category_select_list')
                        {{-- サブカテゴリー --}}
                        @include('common.index.sub_category_select_list')
                    </div>

                    {{-- 相手先商品番号 --}}
                    @include('common.master.index.customer_product_code')

                    {{-- 種別 --}}
                    @include('common.index.kind_select_list')

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- 分類１ --}}
                        @include('common.index.classification1_select_list')
                        {{-- 分類２ --}}
                        @include('common.index.classification2_select_list')
                    </div>

                    {{-- 軽減税率フラグ --}}
                    @include('common.master.index.reduced_tax_flag')
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
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}">
                    <input type="hidden" name="name_kana" value="{{ $search_condition_input_data['name_kana'] ?? '' }}">
                    <input type="hidden" name="category_id" value="{{ $search_condition_input_data['category_id'] ?? '' }}">
                    <input type="hidden" name="sub_category_id" value="{{ $search_condition_input_data['sub_category_id'] ?? '' }}">
                    <input type="hidden" name="customer_product_code" value="{{ $search_condition_input_data['customer_product_code'] ?? '' }}">
                    <input type="hidden" name="kind_id" value="{{ $search_condition_input_data['kind_id'] ?? '' }}">
                    <input type="hidden" name="classification1_id" value="{{ $search_condition_input_data['classification1_id'] ?? '' }}">
                    <input type="hidden" name="classification2_id" value="{{ $search_condition_input_data['classification2_id'] ?? '' }}">
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success"
                   onclick="downloadPost('{{ route('master.products.download_excel')}}');">
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
                    {{ $search_result['products']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    <a class="btn btn-primary" href="{{ route('master.products.create') }}">
                        <i class="far fa-plus-square"></i>
                        新規登録
                    </a>
                </div>
            </div>

            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th rowspan=2 scope="col"></th>
                        <th rowspan=2 scope="col" class="col-md-1 align-middle">コード</th>
                        <th scope="col" class="col-md-4">商品名</th>
                        <th rowspan=2 scope="col" class="col-md-1 align-middle">JANコード</th>
                        <th rowspan=2 scope="col" class="col-md-1 align-middle">税区分</th>
                        <th rowspan=2 scope="col" class="col-md-1 align-middle">単価</th>
                        <th rowspan=2 scope="col" class="col-md-1 align-middle">仕入単価</th>
                        <th rowspan=2 scope="col" class="col-md-1 align-middle">相手先商品番号</th>
                        <th rowspan=2 scope="col" class="col-md-1 align-middle">種別</th>
                        <th scope="col" class="col-md-1">分類１</th>
                    </tr>
                    <tr class="text-center">
                        <th scope="col" class="col-md-4">カテゴリー</th>
                        <th scope="col" class="col-md-1">分類２</th>
                    </tr>

                    </thead>
                    <tbody>
                    @foreach ($search_result['products'] ?? [] as $key => $product)
                        <tr>
                            <th rowspan=2 scope="row" class="text-center align-middle" onclick="checkingClickedOrNot('master.products');"
                                data-title="編集">
                                <a href="{{ route('master.products.edit', $product->id) }}">
                                    <label class="btn btn-outline-info m-0">編集</label>
                                </a>
                            </th>
                            <td rowspan=2 class="text-center align-middle" data-title="コード">
                                <div>{{ $product->code_zerofill }}</div>
                            </td>
                            <td class="text-left align-middle" data-title="商品名">
                                <div style="font-size: 0.6rem;">{{ $product->name_kana }}&nbsp;</div>
                                <div style="font-size: 1.0rem;font-weight: bold">{{ $product->name }}</div>
                            </td>
                            <td rowspan=2 class="text-left align-middle" data-title="JANコード">
                                <div>{{ $product->jan_code }}</div>
                            </td>
                            <td rowspan=2 class="text-center align-middle" data-title="税区分">
                                <div>
                                    {{ $search_items['tax_types'][$product->tax_type_id] }}<br>
                                    @if ($product->tax_type_id !== TaxType::TAX_EXEMPT)
                                        @if ($product->reduced_tax_flag === ReducedTaxFlagType::REDUCED_TAX)
                                            {{ ReducedTaxFlagType::getDescription(ReducedTaxFlagType::REDUCED_TAX) }}
                                        @else
                                            {{ ReducedTaxFlagType::getDescription(ReducedTaxFlagType::NOT_REDUCED_TAX) }}
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td rowspan=2 class="text-right align-middle" data-title="単価">
                                <div style="font-size: 1.2rem;font-weight: bold;">
                                    {{ number_format($product->unit_price_floor) }}
                                </div>
                            </td>
                            <td rowspan=2 class="text-right align-middle" data-title="仕入単価">
                                <div style="font-size: 1.2rem;font-weight: bold">{{ number_format($product->purchase_unit_price_floor) }}</div>

                            </td>
                            <td rowspan=2 class="text-left align-middle" data-title="商品コード">
                                <div>{{ $product->customer_product_code }}</div>
                                <div style="font-size: 0.8rem">{{ $product->supplier_name }}</div>
                            </td>
                            <td rowspan=2 class="text-left align-middle" data-title="種別">
                                <div>
                                    {{ $product->kind_name }}
                                </div>
                            </td>
                            <td class="text-left align-middle" data-title="分類１">
                                <div>
                                    {{ $product->classification1_name }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-left align-middle" data-title="カテゴリ">
                                <div style="font-size: 0.8rem;">{{ $product->full_category_name }}&nbsp;</div>
                            </td>

                            <td class="text-left align-middle" data-title="分類２">
                                <div>
                                    {{ $product->classification2_name }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['products']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/products/index.js') }}"></script>
@endsection
