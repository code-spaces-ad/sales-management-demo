{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar', ['active' => '/report_output/accounts_receivable_journal'])
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
                    {{-- 年月度 --}}
                    @include('common.index.order_date')

                    {{-- 部門(隠し) --}}
                    <input type="hidden" id="department_id" name="department_id" class="input-department-id"
                           value={{old('department_id', $target_record_data['department_id'] ?? null)}}>

                    {{-- 事業所 --}}
                    @include('common.index.office_facility_select_list', ['required_office_facility' => true])

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
    <script src="{{ mix('js/app/trading/index.js') }}"></script>
@endsection
