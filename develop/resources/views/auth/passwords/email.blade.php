{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.footer')

@section('title', 'パスワードを忘れた方 | ' . config('app.name'))

@section('content')
    <div class="login-area col-md-12">
        <div class="login-box ">
            {{-- システムタイトル --}}
            <div class="text-center" style="font-size: 2rem;">
                <div>{{ config('app.company_name') }}</div>
                <div>{{ config('app.name') }}</div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" class="form-reset-password" action="{{ route('password.email') }}">
                        @csrf

                        {{-- メールアドレス --}}
                        <div class="input-group mb-3">
                            <div class="form-label-group w-100 mb-0">
                                <input type="text" name="email" id="email"
                                       class="{{ $errors->has('email') ? 'form-control is-invalid' : 'form-control' }}"
                                       placeholder="メールアドレス" autofocus required>

                                <label for="email">メールアドレス</label>
                            </div>
                            @error('email')
                                <div class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                {{ __('passwords.reset_send_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

        .form-reset-password {
            margin: auto;
            padding: 15px 0;
            width: 100%;
            max-width: 330px;
        }
    </style>
@endsection
