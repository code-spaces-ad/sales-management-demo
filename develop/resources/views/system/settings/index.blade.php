{{-- 設定画面用Blade --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    use App\Helpers\SettingsHelper;
@endphp

{{-- JavaScript --}}
<script src="{{ asset(mix('js/app/system/settings/index.js')) }}"></script>

@php
    $next_url      = route('system.settings.store');
    $next_btn_text = '設定を更新';
    $method        = 'POST';
@endphp

@section('title', '各種設定 | ' . config('app.name'))
@section('headline', '各種設定')

<style>
    table tr:nth-child(even) td {
        background: white !important;
    }

    .card-body {
        position: relative;
    }

    .card-body-disabled {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 2;
        width: 100%;
        height: 100%;
        background: #cacaca70
    }
</style>

@section('content')

    <form id="editForm" name="editForm" action="{{ $next_url }}" method="{{ $method }}" files="true">
        @csrf
        <div class="m-0 p-0" style="border-radius: 0.25rem; border: 3px solid #9ea0a2; background-color: #ffffff;">
            <div class="list-group list-group-horizontal" style="border-radius: 0 !important;">
                @if (auth()->user()->role_id === UserRoleType::SYS_ADMIN)
                    <a href="#"
                       class="list-group-item list-group-item-action list-group-item-light select-tab active"
                       style="border: 1px solid lightpink; border-radius: 0 !important; background-color: lightpink !important; color: black;"
                       id="admin">システム管理者設定</a>
                    <a href="#"
                       class="list-group-item list-group-item-action list-group-item-light select-tab active"
                       style="border: 1px solid lightpink; border-radius: 0 !important; background-color: lightpink !important; color: black;"
                       id="report">帳票(システム管理者)</a>
                @endif
            </div>

            @if (auth()->user()->role_id === UserRoleType::SYS_ADMIN)
                {{-- システム管理者設定 --}}
                @include('system.settings.admin')
                {{-- システム管理者設定 --}}
                @include('system.settings.report')
            @endif
        </div>

        <div class="col-md-10 my-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="buttons-area text-center">
                        <input type="submit" value="{{ htmlspecialchars($next_btn_text, ENT_QUOTES, 'UTF-8') }}" class="btn btn-primary">
                    </div>
                </div>
            </div>
        </div>

    </form>

@endsection
