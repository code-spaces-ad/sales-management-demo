{{-- 在庫確認画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $next_url = route('inventory.stocks.index');
    $method = 'GET';
    /** @see MasterProductsConst */
    $maxlength_product_code = MasterProductsConst::CODE_MAX_LENGTH;   // 商品コード最大桁数
@endphp

@section('title', '在庫確認 | ' . config('app.name'))
@section('headline', '在庫確認')

@section('content')
    <div class="row">
        <form name="searchForm" action="{{ $next_url }}" method="GET" class="col-12 px-0 px-md-4 pb-md-2">
            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- 商品名 --}}
                    @include('common.index.product_select_list', ['required_product' => true])
                </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>
        </form>

        <div class="col-md-10 d-flex justify-content-between mb-2">
            <div class="download-area">
                <form name="downloadForm" id="download_form" action="" method="GET">
                    <input type="hidden" name="id[start]" value="{{ old('id.start', $search_condition_input_data['id']['start'] ?? '') }}">
                    <input type="hidden" name="id[end]" value="{{ old('id.end', $search_condition_input_data['id']['end'] ?? '') }}">
                    <input type="hidden" name="code[start]" value="{{ old('code.start', $search_condition_input_data['code']['start'] ?? '') }}">
                    <input type="hidden" name="code[end]" value="{{ old('code.end', $search_condition_input_data['code']['end'] ?? '') }}">
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}">
                    <input type="hidden" name="name_kana" value="{{ $search_condition_input_data['name_kana'] ?? '' }}">
                </form>

                <script>
                    function downloadPost(url) {
                        'use strict';

                        document.getElementById('download_form').setAttribute('action', url);
                        document.getElementById('download_form').submit();
                    }
                </script>
            </div>
            <br>
        </div>

        <div class="col-md-12">
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list" id="inventory_stock_table">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th colspan="2" class="border-0" style="background-color: transparent;"></th>
                        <th>合計</th>
                    </tr>
                    <tr style="background-color: transparent;">
                        <td colspan="2" class="border-0" style="background-color: transparent;"></td>
                        <td class="text-right align-middle border-width"
                            style="background-color: transparent;" data-title="合計">
                            {{ round($search_result['inventory_stock_total']) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0" style="height: 10px"></td>
                    </tr>
                    <tr class="text-center">
                        <th style="width: 4%">No.</th>
                        <th style="width: 50%">倉庫名</th>
                        <th style="width: 20%">数量</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['inventory_stock_datas'] ?? [] as $key => $inventory_stock_data)
                        <tr>
                            {{-- No. --}}
                            <td class="text-center align-middle sphone-no-display">
                                {{ $key + 1}}
                            </td>
                            <th class="text-center align-middle pc-no-display">
                                {{ $key + 1 }}
                            </th>
                            <td class="text-left" data-title="倉庫名">
                                <div style="font-size: 1.2em;">
                                    {{ $inventory_stock_data->mWarehouses->name }}
                                </div>
                            </td>
                            <td class="text-right text_inventory_stocks" data-title="数量">
                                <div style="font-size: 1.2em;">
                                    {{ round($inventory_stock_data->sum_inventory_stocks) }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/inventory/index.js') }}"></script>
    <script src="{{ mix('js/app/inventory/stocks/index.js') }}"></script>

@endsection
