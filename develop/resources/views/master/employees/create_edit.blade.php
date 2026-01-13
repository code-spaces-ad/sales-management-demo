{{-- 担当マスター登録・編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.employees.create');
    $next_url = route('master.employees.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    if ((Route::currentRouteName() === 'master.employees.edit')) {
        $headline = config('consts.title.master.menu.employees.edit');
        $next_url = route('master.employees.update', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
    }
    // デフォルトMAX日付
    $default_max_date = config('consts.default.common.default_max_date');
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
                        <label class="col-md-2 col-form-label">
                            <b>コード</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-2">
                            <input type="text" name="code"
                                value="{{ old('code', $target_record_data['code_zerofill'] ?? '') }}"
                                class="form-control input-code{{ $errors->has('code') ? ' is-invalid' : '' }}">

                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button id="code-spinner" type="button" class="btn btn-primary"
                                onclick="searchAvailableNumber('employees')">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                 style="display: none;"></div>
                            利用可能コード
                        </button>
                    </div>

                    {{-- 社員名 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>社員名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="name"
                                value="{{ old('name', $target_record_data['name'] ?? '') }}"
                                class="form-control input-name{{ $errors->has('name') ? ' is-invalid' : '' }}">

                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 社員名（かな） --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>社員名（カナ）</b>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="name_kana"
                                value="{{ old('name_kana', $target_record_data['name_kana'] ?? '') }}"
                                class="form-control input-name-kana{{ $errors->has('name_kana') ? ' is-invalid' : '' }}">

                            @error('name_kana')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 部門 --}}
                    @include('common.create_edit.department_select_list')
                    {{-- 事業所 --}}
                    @include('common.create_edit.office_facility_select_list')

                    {{-- 生年月日 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>生年月日</b>
                        </label>
                        <div class="col-sm-4">
                            <input type="date" name="birthday" id="date_birthday"
                                value="{{ old('birthday', $target_record_data['birthday'] ?? '') }}"
                                class="form-control input-birthday{{ $errors->has('birthday') ? ' is-invalid' : '' }}"
                                max="{{ $default_max_date }}">

                            @error('birthday')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 入社日 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>入社日</b>
                        </label>
                        <div class="col-sm-4">
                            <input type="date" name="hire_date"
                                value="{{ old('hire_date', $target_record_data['hire_date'] ?? '') }}"
                                class="form-control input-hire-date{{ $errors->has('hire_date') ? ' is-invalid' : '' }}"
                                max="{{ $default_max_date }}">

                            @error('hire_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 備考 --}}
                    <div class="form-group row my-3">
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
                        {{-- 一覧画面へ戻るボタン --}}
                        <a id="return" class="btn btn-primary back_active"
                        href="{{ session($session_master_key, route('master.employees.index')) }}">
                            一覧画面へ戻る
                        </a>

                        @if (config('consts.default.common.use_register_clear_button'))
                            {{-- クリアボタン --}}
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
                            {{$next_btn_text}}
                        </button>

                        @if ($is_edit_route)
                            {{-- 削除ボタン --}}
                            <button id="delete" type="button"
                                    class="btn btn-danger"
                                    data-toggle="modal"
                                    data-target="#confirm-delete"
                                    @if ($target_record_data['use_master']) disabled @endif>
                                <i class="fas fa-times"></i>
                                <div class="spinner-border spinner-border-sm text-light align-middle"
                                    role="status"
                                    style="display: none;"></div>
                                @if ($target_record_data['use_master'])
                                    使用中のため削除不可
                                @else
                                    削除
                                @endif
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div>
        {{-- API --}}
        <input type="hidden" name="api_get_next_usable_code_url"
            value="{{ route('api.common.get_next_usable_code') }}"
            class="hidden-api-get-next-usable-code-url">

    </div>

    @if ($is_edit_route)
        <form action="{{ route('master.employees.destroy', $target_record_data['id']) }}" name="deleteForm" id="deleteForm" method="POST">
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
    <script src="{{ mix('js/app/master/employees/create_edit.js') }}"></script>
@endsection
