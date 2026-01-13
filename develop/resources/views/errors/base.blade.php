{{-- エラーベース画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $sidebar_toggled = '';
    $time_trans = 10;   // トップページに遷移するまでの表示時間（秒）
@endphp

@section('title', 'エラー | ' . config('app.name'))
@section('headline', 'エラー')

@section('content')
    <div class="code">
        @yield('code')
    </div>
    <div class="message">
        @yield('message')
    </div>
    <div>
        <a class="top-page-button" href="{{ url('/') }}">
            トップページに戻る
        </a>
        <div>※表示後、{{ $time_trans }}秒後にトップページに遷移します。</div>
    </div>

    <script>
        /**
         * 表示後、○秒後にトップページに遷移
         */
        setTimeout(function () {
            window.location.href = '{{ url('/') }}';
        }, {{ $time_trans }} * 1000);
    </script>
@endsection
