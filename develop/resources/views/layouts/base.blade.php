{{-- ベース用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @yield('head')
</head>

<script>
    window.Laravel = {
        enums: @json($jsEnums)
    };
</script>

@php
    $sidebar_toggled = '';  // サイドバー用toggle
    // ※ログイン画面は対象外
    if (View::hasSection('sidebar') && !Request::is('login')) {
        $agent = app(Laravel\Jetstream\Agent::class);
        if ($agent->isDesktop()) {
            // PC版の場合、サイドバーは初期表示状態にする
            $sidebar_toggled = 'toggled';
        }
    }

    // ログイン時の背景画像
    $disp_background_image = false;
    $background_image = null;
    if (Request::is('login') || Request::is('password/reset') || Request::is('password/reset/*')) {
        $disp_background_image = true;
        $images = config('consts.default.common.login_bg_images');
        $background_image = $images[array_rand($images)];
    }
@endphp

<body>
<style type="text/css">
    <!--
    .login-background-image {
        background-size: cover;
        background-attachment: fixed;
        background-position: center center;
        background-color: #1b4b72;
    }
    -->
</style>
<div class="page-wrapper chiller-theme {{ $sidebar_toggled }} @if($disp_background_image) login-background-image @endif"
    @if (config("app.env") === "staging")
        style="background-color: lavenderblush;"
    @endif>
    {{-- サイドバー --}}
    @yield('sidebar')

    <main class="page-content" id="page_content_id">
        <div class="container-fluid">
            <div class="main-content">
                {{-- ヘッダー --}}
                @yield('header')

                {{-- header-alert --}}
                @if (session('message'))
                    <div
                        class="alert alert-dismissible fade show @if (session('error_flag')) alert-danger @else alert-success @endif"
                        role="alert">
                        @if (session('error_flag'))
                            <i class="fas fa-exclamation-circle"></i>
                        @else
                            <i class="fas fa-check"></i>
                        @endif
                        {!! nl2br(e(session('message'))) !!}
                        {{-- 閉じるボタン --}}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{-- コンテンツ --}}
                @yield('content')
            </div>
        </div>
    </main>

    {{-- フッター --}}
    @yield('footer')
</div>
</body>
</html>
