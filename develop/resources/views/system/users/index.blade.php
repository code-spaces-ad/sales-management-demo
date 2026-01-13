{{-- ユーザーマスター一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.system.menu.users.index');
    $next_url = route('system.users.index');
    $method = 'GET';
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <div class="col-md-12 pl-0">
            <form name="searchForm" action="{{ $next_url }}" method="{{ $method }}">
                {{-- コード --}}
                <div class="form-group row col-md-6 my-1">
                    <label class="col-sm-3 col-form-label">
                        <b>コード</b>
                    </label>
                    <div class="col-sm-9 row">
                        <div class="col-sm-4 input-tilde">

                            <input type="number" name="code[start]" id="code_start"
                                class="form-control input-code-start {{ $errors->has('code.start') ? 'is-invalid' : '' }}"
                                value="{{ old('code.start', $search_condition_input_data['code']['start'] ?? '') }}" min="1" />

                        </div>
                        <div class="col-sm-5">

                            <input type="number" name="code[end]" id="code_end"
                                class="form-control input-code-end {{ $errors->has('code.end') ? ' is-invalid' : '' }}"
                                value="{{ old('code.end', $search_condition_input_data['code']['end'] ?? '') }}" min="1" />

                        </div>
                        @error('code.*')
                        <div class="invalid-feedback ml-3">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row col-md-6 my-3">
                    <label class="col-sm-3 col-form-label">
                        <b>ログインID</b>
                    </label>
                    <div class="col-sm-9">

                        <input type="text" name="login_id"
                            class="form-control input-login-id {{ $errors->has('login_id') ? 'is-invalid' : '' }}"
                            value="{{ $search_condition_input_data['login_id'] ?? '' }}" />

                        @error('login_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row col-md-6 my-3">
                    <label class="col-sm-3 col-form-label">
                        <b>名前</b>
                    </label>
                    <div class="col-sm-9">

                        <input type="text" name="name"
                            class="form-control input-name {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ $search_condition_input_data['name'] ?? '' }}" />

                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearInput();">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>
                    <button type="submit" value="検索" class="btn btn-primary">
                        <i class="fas fa-search"></i> 検索
                    </button>
                </div>

            </form>
        </div>

        <div class="col-md-12 d-flex justify-content-between mb-2">
            <div class="download-area">
                <form action="" name="downloadForm" id="download_form" method="POST">
                @method($method)
                @csrf
                    <input type="hidden" name="id[start]" value="{{ old('id.start', $search_condition_input_data['id']['start'] ?? '') }}">
                    <input type="hidden" name="id[end]" value="{{ old('id.end', $search_condition_input_data['id']['end'] ?? '') }}">
                    <input type="hidden" name="code[start]" value="{{ old('code.start', $search_condition_input_data['code']['start'] ?? '') }}">
                    <input type="hidden" name="code[end]" value="{{ old('code.start', $search_condition_input_data['code']['end'] ?? '') }}">
                    <input type="hidden" name="login_id" value="{{ $search_condition_input_data['login_id'] ?? '' }}">
                    <input type="hidden" name="name" value="{{ $search_condition_input_data['name'] ?? '' }}">
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success"
                   onclick="downloadPost('{{ route('system.users.download_excel')}}');">
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

            <div class="">
                <a class="btn btn-primary" href="{{ route('system.users.create') }}">
                    <i class="far fa-plus-square"></i>
                    新規登録
                </a>
            </div>
        </div>

        <div class="col-md-12">
            {{ $search_result['users']->appends($search_condition_input_data)->links() }}
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th style="width: 20%">コード</th>
                        <th style="width: 20%">ログインID</th>
                        <th style="width: 60%">名前</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['users'] ?? [] as $key => $user)
                        <tr>
                            <td class="text-center" data-title="コード" onclick="checkingClickedOrNot('system.users');">
                                <a href="{{ route('system.users.edit', $user->id) }}">{{ $user->code_zerofill }}</a>
                            </td>
                            <td class="text-left sphone-no-display" data-title="ログインID"
                                style="width: 20%">{{ $user->login_id }}</td>
                            <td class="text-left pc-no-display" data-title="ログインID">{{ $user->login_id }}</td>
                            <td class="text-left" data-title="名前">{{ $user->name }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['users']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/system/users/index.js') }}"></script>
@endsection
