{{-- ログイン画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.footer')

@section('title', 'ログイン | ' . config('app.name'))

@section('content')
    <div class="login-area col-md-12">
        <div class="login-box ">
            {{-- システムタイトル --}}
            <div class="text-center" style="font-size: 2rem;">
                <div>{{ config('app.company_name') }}</div>
                <div>{{ config('app.name') }}</div>
            </div>

            <form name="loginForm" id="loginForm" class="form-signin" action="{{ route('login') }}" method="POST">
                @csrf

                {{-- ログインID 項目 --}}
                <div class="input-group mb-3">
                    <div class="form-label-group w-100 mb-0">
                        <input type="text" name="login_id" id="login_id"
                            class="{{ $errors->has('login_id') ? 'form-control is-invalid' : 'form-control' }}"
                            placeholder="ログインID" autofocus required>

                        <label for="login_id">ログインID</label>
                    </div>
                    @error('login_id')
                    <div class="invalid-feedback"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>

                {{-- パスワード 項目 --}}
                <div class="input-group mb-3">
                    <div class="form-label-group w-100 mb-0">
                        <input type="password" name="password" id="password"
                            class="{{ $errors->has('password') ? 'form-control is-invalid' : 'form-control' }}"
                            placeholder="パスワード" autocomplete="off" required onkeydown="onKeyDown();">
                        <label for="password">パスワード</label>
                    </div>
                    @error('password')
                    <div class="invalid-feedback"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>

                <div class="col-sm-12 text-center mb-3">
                    <div class="icheck-primary icheck-inline">
                        <input type="checkbox" name="remember_me" value="1" id="remember_me"
                               class="form-check-input input-remember">
                        <label class="form-check-label" for="remember_me">ログイン名を保存する</label>
                    </div>
                </div>

                {{-- ログインボタン --}}
                <div class="text-center">
                    <button id="login" type="button" class="btn btn-lg btn-primary btn-block"
                            onclick="loginProcess(); isCheckboxClicked();">
                        <i class="fas fa-unlock-alt"></i>
                        <div class="spinner-border text-light" role="status" style="display: none;"></div>
                        ログイン
                    </button>
                </div>
            </form>

            {{-- Password reset link --}}
            <div class="card-footer">
                <p class="my-0">
                    <a href="{{ route('password.request') }}" class="reset-password">
                        {{ __('passwords.reset_link') }}
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('load', function () {
            if (getCookie("checkBoxValue") == 'true') {
                $("#remember_me").prop('checked', true);
            } else {
                $("#remember_me").prop('checked', false);
            }
            let setUserId = getCookie("userName");
            $("#login_id").val(setUserId);
        });

        function getCookie(cookieName) {
            let cookie = {};
            document.cookie.split(';').forEach(function (el) {
                let [key, value] = el.split('=');
                cookie[key.trim()] = value;
            })
            return cookie[cookieName];
        }

        function isCheckboxClicked() {
            if ($("#remember_me").is(':checked')) {
                let getUserId = $("#login_id").val();
                document.cookie = "userName=" + getUserId;
                document.cookie = "checkBoxValue=" + 'true';
                console.log(getCookie("userName"));
            } else {
                document.cookie = "userName=" + " ";
                document.cookie = "checkBoxValue=" + 'false';
            }
        }
    </script>


    <script>
        /**
         * キーダウンイベント処理
         */
        function onKeyDown() {
            // Enter キーの時だけ
            if (event.which == 13) {
                // これ以降のイベントはキャンセルしておく
                event.stopImmediatePropagation();
                event.preventDefault();

                // ログイン Submit
                document.getElementById('loginForm').submit();
            }
        }

        /**
         * ログイン処理
         */
        function loginProcess() {
            // ボタン非活性
            $('#login').prop('disabled', true);
            // ローディング切替
            $('#login i').hide();
            $('#login div').show();
            // ログイン処理
            $('#loginForm').submit();
        }
    </script>

    <style type="text/css">
        @media (min-width: 751px) {
            .login-box {
                padding: 2.5em 1em;
                margin: auto;

                width: 600px;
                background: #FFF;
                border-radius: 10px;
            }
        }

        @media (max-width: 750px) {
            .login-box {
                padding: 2.5em 1em;
                margin: auto;

                background: #FFF;
                border-radius: 10px;
            }
        }

        .reset-password {
            font-size: 1rem;
            color: #29a25c;
        }
    </style>
@endsection
