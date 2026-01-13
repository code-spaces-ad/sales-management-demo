{{-- 支所マスター一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.branches.index');
    $next_url = route('master.branches.index');
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
                    {{-- 得意先 --}}
                    @include('common.index.customer_select_list')

                    {{-- 支所名 --}}
                    @include('common.master.index.branch_name')
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
                    {{-- ※hidden項目は、検索項目と合わせること。 --}}
                    <input type="hidden" name="customer_id" value="{{ $search_condition_input_data['customer_id'] ?? '' }}">
                    <input type="hidden" name="customer_id_code" value="{{ $search_condition_input_data['customer_id_code'] ?? '' }}">
                    <input type="hidden" name="branch_name" value="{{ $search_condition_input_data['branch_name'] ?? '' }}">
                </form>

                    {{-- Excelダウンロードボタン --}}
                    <a class="btn btn-success"
                    onclick="downloadPost('{{ route('master.branches.download_excel')}}');">
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
                    {{ $search_result['branches']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    <a class="btn btn-primary" href="{{ route('master.branches.create') }}">
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
                        <th scope="col" class="col-md-6">支所名</th>
                        <th scope="col" class="col-md-6">支所名略称</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['branches'] ?? [] as $key => $branch)
                        <tr>
                            <th scope="col" class="text-center align-middle" onclick="checkingClickedOrNot('master.branches');"
                                data-title="編集">
                                <a href="{{ route('master.branches.edit', $branch->branch_id) }}">
                                    <label class="btn btn-outline-info m-0">編集</label>
                                </a>
                            </th>
                            <td class="text-left align-middle" data-title="支所名">
                                <div style="font-size: 0.8rem;">{{StringHelper::getNameWithId($branch->customer_code_zerofill,$branch->customer_name)}}</div>
                                <div style="font-size: 0.6rem;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $branch->name_kana }}</div>
                                <div style="font-size: 1.0rem;font-weight: bold">┗{{$branch->branch_name}}</div>
                            </td>
                            <td class="text-left align-middle" data-title="支所名略称">
                                {{ $branch->mnemonic_name }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['branches']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/index.js') }}"></script>
    <script src="{{ mix('js/app/master/branches/index.js') }}"></script>
@endsection
