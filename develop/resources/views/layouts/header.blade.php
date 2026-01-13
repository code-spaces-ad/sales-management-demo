{{-- ヘッダー用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@section('header')
    <div class="main-header">
        {{-- ページタイトル Filed --}}
        <div class="headline" style="width:60%;">
            <h1>@section('headline') @show</h1>
        </div>
        @if(Auth::check())
            {{-- ログイン名、ログアウトボタン Filed --}}
            <div class="login-user-name" style="width:40%;">
                {{-- ログイン名 --}}
                <span>
                    {{ Auth::user()->name ?? null }}
                </span>

                {{-- ログアウトボタン --}}
                <button type="button" id="logout" class="btn btn-primary ml-2" onclick="logout();return false;">
                    <i class="fas fa-sign-out-alt"></i>
                    <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                         style="display: none;"></div>
                    ログアウト
                </button>

                <form id="logoutForm" name="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                </form>

            </div>
        @endif
    </div>

    <script>
        /**
         * 登録更新処理
         */
        function logout() {
            // ボタン非活性
            disableButtons();
            // ローディング切替
            changeLoading();

            // submit処理
            $('#logoutForm').submit();
        }

        /**
         * ボタン非活性
         */
        function disableButtons() {
            // ボタン非活性
            $('#logout').prop('disabled', true);
        }

        /**
         * ローディング切替(登録・更新）
         */
        function changeLoading() {
            // ローディング切替
            $('#logout i').hide();
            $('#logout div').show();
        }
    </script>
@endsection
