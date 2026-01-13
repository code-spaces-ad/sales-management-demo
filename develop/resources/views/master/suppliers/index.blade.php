{{-- 仕入先マスター一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.suppliers.index');
    $next_url = route('master.suppliers.index');
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
                    @include('common.master.index.closing_date_code')

                    {{-- 仕入先名 --}}
                    @include('common.master.index.supplier_name')

                    {{-- 仕入締日 --}}
                    @include('common.master.index.supplier_closing_date')
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
                    <input type="hidden" name="code[start]" value="{{ old('code.start', $search_condition_input_data['code']['start'] ?? '') }}">
                    <input type="hidden" name="code[end]" value="{{ old('code.end', $search_condition_input_data['code']['end'] ?? '') }}">
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}">
                    @foreach(($search_condition_input_data['closing_date'] ?? []) as $closing_date)
                        <input type="hidden" name="closing_date[]" value="{{ $closing_date ?? '' }}">
                    @endforeach
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success"
                   onclick="downloadPost('{{ route('master.suppliers.download_excel')}}');">
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
                    {{ $search_result['suppliers']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    <a class="btn btn-primary" href="{{ route('master.suppliers.create') }}">
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
                        <th scope="col" class="col-md-4">仕入先名</th>
                        <th scope="col" class="col-md-4">住所</th>
                        <th scope="col" class="col-md-1">税区分</th>
                        <th scope="col" class="col-md-2">仕入締日</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['suppliers'] ?? [] as $key => $supplier)
                        <tr>
                            <th scope="row" class="text-center align-middle" onclick="checkingClickedOrNot('master.suppliers');"
                                data-title="編集">
                                <a href="{{ route('master.suppliers.edit', $supplier->id) }}">
                                    <label class="btn btn-outline-info m-0">編集</label>
                                </a>
                            </th>
                            <td class="text-center align-middle" data-title="コード">
                                {{$supplier->code_zerofill}}
                            </td>
                            <td class="text-left align-middle" data-title="仕入先名">
                                <div style="font-size: 0.6rem;">{{ $supplier->name_kana }}&nbsp;</div>
                                <div style="font-size: 1.0rem;font-weight: bold">{{ $supplier->name }}</div>
                            </td>
                            <td class="text-left align-middle" data-title="住所">
                                <div>
                                    @if ( !is_null($supplier->postal_code1))
                                        〒{{ $supplier->postal_code1.'-'.$supplier->postal_code2 }}
                                    @else
                                        &nbsp;
                                    @endif
                                </div>
                                <div>
                                    {{ ($supplier->address1 ?? '').($supplier->address2 ?? '') }}
                                    @if ( !is_null($supplier->address1))
                                        <a href="{{config('consts.default.api.google_address_search').($supplier->address1 ?? '').($supplier->address2 ?? '')}}"
                                           target="_blank">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    @else
                                        &nbsp;
                                    @endif
                                </div>
                            </td>
                            <td class="text-center align-middle" data-title="税区分">
                                {{ \App\Enums\TaxCalcType::asSelectArray()[$supplier->tax_calc_type_id]}}
                            </td>
                            <td class="text-left align-middle" data-title="仕入締日">
                                <div>{{ config('consts.default.common.closing_date_list')[$supplier->closing_date] }}
                                    日締め
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['suppliers']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/suppliers/index.js') }}"></script>
@endsection
