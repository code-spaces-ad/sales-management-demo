{{-- 分類2マスター一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    use App\Enums\IsControlInventory;

    $headline = config('consts.title.master.menu.classifications2.index');
    $next_url = route('master.classifications2.index');
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
                    {{-- ID --}}
                    <div class="form-group row col-md-6 my-1" style="display: none;">
                        <label class="col-sm-3 col-form-label">
                            <b>ID</b>
                        </label>
                        <div class="col-sm-9 row">
                            <div class="col-sm-5 input-tilde">
                                <input type="number" name="id[start]" id="id_start"
                                    value="{{ old('id.start', $search_condition_input_data['id']['start'] ?? '') }}"
                                    class="form-control input-id-start{{ $errors->has('id.start') ? ' is-invalid' : '' }}">
                            </div>
                            <div class="col-sm-5">
                                <input type="number" name="id[end]" value="{{ old('id.end', $search_condition_input_data['id']['end'] ?? '') }}"
                                    class="form-control input-id-end{{ $errors->has('id.end') ? ' is-invalid' : '' }}">
                            </div>
                            @error('id.*')
                            <div class="invalid-feedback ml-3">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- コード --}}
                    @include('common.master.index.code')

                    {{-- 分類2名 --}}
                    @include('common.master.index.classification2_name')
                </div>

                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>
        </form>
        <div class="col-md-12 d-flex justify-content-between mb-2">
            <div class="download-area">
                <form action="" name="downloadForm" id="download_form" method="GET">
                @csrf
                    <input type="hidden" name="id[start]" value="{{ old('id.start', $search_condition_input_data['id']['start'] ?? '') }}">
                    <input type="hidden" name="id[end]" value="{{ old('id.end', $search_condition_input_data['id']['end'] ?? '') }}">
                    <input type="hidden" name="code[start]" value="{{ old('code.start', $search_condition_input_data['code']['start'] ?? '') }}">
                    <input type="hidden" name="code[end]" value="{{ old('code.end', $search_condition_input_data['code']['end'] ?? '') }}">
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}">
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success"
                   onclick="downloadPost('{{ route('master.classifications2.download_excel')}}');">
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
                    {{ $search_result['classifications2']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    <a class="btn btn-primary" href="{{ route('master.classifications2.create') }}">
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
                        <th scope="col" class="col-md-11">分類2名</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['classifications2'] ?? [] as $key => $classification2)
                        <tr>
                            <th scope="row" class="text-center align-middle" onclick="checkingClickedOrNot('master.classifications2');"
                                data-title="編集">
                                <a href="{{ route('master.classifications2.edit', $classification2->id) }}">
                                    <label class="btn btn-outline-info m-0">編集</label>
                                </a>
                            </th>
                            <td class="text-center align-middle" data-title="コード">
                                {{ $classification2->code_zerofill }}
                            </td>
                            <td class="text-left align-middle" data-title="分類2名">
                                <div style="font-size: 1.0rem;font-weight: bold">{{ $classification2->name }}</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['classifications2']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/classifications2/index.js') }}"></script>
@endsection
