{{-- ユーザーマスター登録・編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.system.menu.users.create');
    $next_url = route('system.users.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    if ((Route::currentRouteName() === 'system.users.edit')) {
        $headline = config('consts.title.system.menu.users.edit');
        $next_url = route('system.users.update', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
    }
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="input-area">
                <form name="editForm" id="editForm" action="{{ $next_url }}" method="POST">
                @method($method)
                @csrf

                    {{-- コード --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>コード</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-2">
                            <input type="text" name="code" value="{{ old('code', $target_record_data['code_zerofill'] ?? '') }}"
                                class="form-control input-code{{ $errors->has('code') ? ' is-invalid' : '' }}">
                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button id="code-spinner" type="button" class="btn btn-primary"
                                onclick="searchAvailableNumber('users')">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                 style="display: none;"></div>
                            利用可能コード
                        </button>
                    </div>

                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>ログインID</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="login_id" value="{{ old('login_id', $target_record_data['login_id'] ?? '' )}}"
                                class="form-control input-login-id{{ $errors->has('login_id') ? ' is-invalid' : '' }}">

                            @error('login_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>パスワード</b>
                            @if (!$is_edit_route)
                                <span class="badge badge-danger">必須</span>
                            @endif
                        </label>
                        <div class="col-sm-5">
                            <input type="password" name="password" class="form-control input-password{{ $errors->has('password') ? ' is-invalid' : '' }}" autocomplete="new-password">

                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- メールアドレス --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>メールアドレス</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="email" value="{{ old('email', $target_record_data['email'] ?? '') }}"
                                   class="form-control input-email{{ $errors->has('email') ? ' is-invalid' : '' }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>名前</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text"
                                   name="name"
                                   value="{{ old( 'name', $target_record_data['name'] ?? '' ) }}"
                                class="form-control input-name{{ $errors->has('name') ? ' is-invalid' : '' }}">

                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>担当紐付け</b>
                        </label>
                        <div class="col-sm-5">
                            {{-- 担当者コード --}}
                            <input type="number"
                                class="form-control input-employee-code h-75 col-5 col-md-4 mr-md-1 d-none"
                                id="employee_code" oninput="inputCode(this);"
                                onchange="changeEmployeeCode(this);">
                            {{-- 担当者 --}}
                            <select name="employee_id" onchange="changeEmployee();"
                                    class="custom-select input-employee-select col-9 px-0 mr-md-1 select2_search d-none
                                    @if($errors->has('employee_id')) is-invalid @endif">
                                <option value="">-----</option>
                                @foreach (($input_items['employees'] ?? []) as $item)
                                    <option
                                        @if ($item['id'] == old('employee_id', $target_record_data['employee_id'] ?? null))
                                            selected
                                        @endif
                                        value="{{ $item['id'] }}"
                                        data-name="{{ $item['name'] }}"
                                        data-name-kana="{{ $item['name_kana'] }}"
                                        data-code="{{ $item['code'] }}">
                                        {{ $item['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 編集画面かつ、従業員でない場合 --}}
                    @if($is_edit_route && !\App\Helpers\UserHelper::isRoleEmployee())
                        {{-- 権限 --}}
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">
                                <b>権限</b>
                                <span class="badge badge-danger">必須</span>
                            </label>
                            <div class="col-sm-2 col-5">
                                <select name="role_id"
                                        class="custom-select input-role_id @if($errors->has('role_id')) is-invalid @endif">
                                    @foreach (($input_items['role_id'] ?? []) as $item)
                                        <option value="{{ $item['id'] }}"
                                            @if ($item['id'] == old('role_id', $target_record_data['role_id'] ?? \App\Enums\UserRoleType::EMPLOYEE)) selected @endif>
                                            {{ $item['name_with_id'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="role_id" value="{{ old('role_id', $target_record_data['role_id'] ?? \App\Enums\UserRoleType::EMPLOYEE) }}">
                    @endif

                    {{-- 備考 --}}
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">
                            <b>備考</b>
                        </label>
                        <div class="col-sm-5">
                            <textarea name="note"
                                      class="form-control input-note{{ $errors->has('note') ? ' is-invalid' : '' }}"
                                      rows="3">{{ old( 'note', $target_record_data['note'] ?? '' ) }}</textarea>

                            @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="buttons-area text-center mt-4">
                        @if(!\App\Helpers\UserHelper::isRoleEmployee())
                            <a id="return" class="btn btn-primary back_active"
                               href="{{ session($session_system_key, route('system.users.index')) }}">
                                一覧画面へ戻る
                            </a>
                        @endif

                        @if (config('consts.default.common.use_register_clear_button'))
                            <button id="clear" type="button" class="btn btn-secondary" onclick="clearInput();">
                                <i class="fas fa-times"></i>
                                クリア
                            </button>
                        @endif

                        {{-- 登録ボタン、更新ボタン --}}
                        <input type="submit" id="btn_submit" value="{{ $next_btn_text }}" class="btn btn-primary" style="display:none;">
                        <button type="button" id="store"
                                class="btn btn-primary"
                                data-toggle="modal"
                                data-target="#confirm-store">
                            <i class="far fa-edit"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle"
                                role="status"
                                style="display: none;"></div>
                            {{ $next_btn_text }}
                        </button>

                        {{-- 管理権限かつ、編集かつ、ログインユーザー以外 --}}
                        @if (!\App\Helpers\UserHelper::isRoleEmployee() && $is_edit_route && !$target_record_data['is_login_user'] )
                            {{-- 削除ボタン --}}
                            <button id="delete" type="button"
                                    class="btn btn-danger"
                                    data-toggle="modal"
                                    data-target="#confirm-delete">
                                <i class="fas fa-times"></i>
                                <div class="spinner-border spinner-border-sm text-light align-middle"
                                    role="status"
                                    style="display: none;"></div>
                                削除
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div>
        {{-- API --}}
        <input type="hidden" name="api_get_next_usable_code_url" value="{{ route('api.common.get_next_usable_code') }}" class="hidden-api-get-next-usable-code-url">
    </div>

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm" action="{{ route('system.users.destroy', $target_record_data['id']) }}" method="POST">
        @method('DELETE')
        @csrf
        </form>
    @endif

    <div>
        {{-- エラー情報 --}}
        <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">
        {{-- 新規/更新 --}}
        <input type="hidden" name="is-edit-route" value="{{ $is_edit_route }}" class="hidden-is-edit-route">
    </div>

    {{-- Confirm Store Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-store')
        @if($is_edit_route)
            @slot('confirm_message', config('consts.message.common.confirm.update') )
        @else
            @slot('confirm_message', config('consts.message.common.confirm.store') )
        @endif
        @slot('onclick_btn_ok', "store();return false;")
    @endcomponent

    {{-- Confirm Delete Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-delete')
        @slot('confirm_message', config('consts.message.common.confirm.delete') )
        @slot('onclick_btn_ok', "destory();return false;")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/create_edit.js') }}"></script>
    <script src="{{ mix('js/app/system/users/create_edit.js') }}"></script>
@endsection
