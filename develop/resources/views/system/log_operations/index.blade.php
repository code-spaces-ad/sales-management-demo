{{-- 操作履歴一覧画面用Blade --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
{{--@extends('layouts.header-alert')--}}
@extends('layouts.footer')

@php
    use Carbon\Carbon;

    $headline = config('consts.title.system.menu.log_operations');
    $next_url = route('system.log_operations.index');
    $method = 'GET';
    // デフォルトMAX日付
    $default_max_date = config('consts.default.common.default_max_date');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <div class="col-md-12 pl-0">

            <form name="searchForm" action="{{ $next_url }}" method="{{ $method }}">

                {{-- ID --}}
                <div class="form-group row col-md-6 my-1" style="display: none;">
                    <label class="col-sm-3 col-form-label">
                        <b>ID</b>
                    </label>
                    <div class="col-sm-9 row">
                        <div class="col-sm-5 input-tilde">

                            <input type="number" name="id[start]" id="id_start"
                                class="form-control input-id-start {{ $errors->has('id.start') ? 'is-invalid' : '' }}"
                                value="{{ old('id.start', $search_condition_input_data['id']['start'] ?? '') }}" />

                        </div>
                        <div class="col-sm-5">

                            <input type="number" name="id[end]"
                                class="form-control input-id-end {{ $errors->has('id.end') ? 'is-invalid' : '' }}"
                                value="{{ old('id.end', $search_condition_input_data['id']['end'] ?? '') }}" />

                        </div>
                        @error('id.*')
                        <div class="invalid-feedback ml-3">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- 操作日時 --}}
                <div class="form-group row col-md-6 my-1">
                    <label class="col-sm-3 col-form-label">
                        <b>操作日時</b>
                    </label>
                    <div class="col-sm-9 row">
                        <div class="col-sm-5 input-tilde">

                            <input type="date" name="created_at[start]" id="created_at_start"
                                class="form-control input-created-at-start clear-value {{ $errors->has('created_at.start') ? 'is-invalid' : '' }}"
                                value="{{ old('created_at.start', $search_condition_input_data['created_at']['start'] ?? '') }}" max="{{ $default_max_date }}" />

                        </div>
                        <div class="col-sm-5">

                            <input type="date" name="created_at[end]" id="created_at_end"
                                class="form-control input-created-at-end clear-value {{ $errors->has('created_at.end') ? ' is-invalid' : '' }}"
                                value="{{ old('created_at.end', $search_condition_input_data['created_at']['end'] ?? '') }}" max="{{ $default_max_date }}" />

                        </div>
                        @error('created_at.*')
                        <div class="invalid-feedback ml-3">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearInput();">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>
                    {{-- 検索ボタン --}}
                    <button type="submit" class="btn btn-primary">
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
                    {{-- ※hidden項目は、検索項目と合わせること。 --}}
                    <input type="hidden" name="created_at[start]" value="{{ old('created_at.start', $search_condition_input_data['created_at']['start'] ?? '') }}">
                    <input type="hidden" name="created_at[end]" value="{{ old('created_at.end', $search_condition_input_data['created_at']['end'] ?? '') }}">
                </form>

                {{-- Excelダウンロードボタン --}}
                <a class="btn btn-success"
                   onclick="downloadPost('{{ route('system.log_operation.download_excel')}}');">
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
            {{ $search_result['log_operations']->appends($search_condition_input_data)->links() }}
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th>操作日時</th>
                        <th>ログインID</th>
                        <th>ユーザ名</th>
                        <th>ルート名</th>
                        <th>要求パス</th>
                        <!-- <th>要求メソッド</th> -->
                        <!-- <th>HTTPステータスコード</th> -->
                        <!-- <th>要求内容</th> -->
                        <th>クライアントIPアドレス</th>
                        <!-- <th>ブラウザ名</th> -->
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['log_operations'] ?? [] as $key => $log)
                        <tr>
                            {{-- No. --}}
                            <th class="text-center align-middle pc-no-display"></th>
                            <td class="text-left"
                                data-title="操作日時">{{ Carbon::parse($log->created_at)->format('Y/m/d H:i:s') }}</td>
                            <td class="text-left" data-title="ログインID">{{ $log->mUser['login_id'] ?? '-' }}</td>
                            <td class="text-left" data-title="ユーザ名">{{ $log->mUser['name'] ?? '-' }}</td>
                            <td class="text-left" data-title="ルート名">{{ $log->route_name }}</td>
                            <td class="text-left" data-title="要求パス">{{ $log->request_url }}</td>
                            <!-- <td class="text-left">{{ $log->request_method }}</td> -->
                            <!-- <td class="text-left">{{ $log->status_code }}</td> -->
                            <!-- <td class="text-left">{{ $log->request_message }}</td> -->
                            <td class="text-left" data-title="クライアントIPアドレス">{{ $log->remote_addr }}</td>
                            <!-- <td class="text-left">{{ $log->user_agent }}</td> -->
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['log_operations']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/system/log_operations/index.js') }}"></script>
@endsection
