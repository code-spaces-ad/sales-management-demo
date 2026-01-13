{{-- 仕入先マスター登録・編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.suppliers.create');
    $next_url = route('master.suppliers.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    if ((Route::currentRouteName() === 'master.suppliers.edit')) {
        $headline = config('consts.title.master.menu.suppliers.edit');
        $next_url = route('master.suppliers.update', $target_record_data['id']);
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
                <form name="editForm" id="editForm" action="{{ $next_url }}" method="POST" onsubmit="return editFormSubmit();">
                @method($method)
                @csrf

                    {{-- コード --}}
                    <div class="form-group row my-1">
                        <label class="col-md-2 col-form-label">
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
                                onclick="searchAvailableNumber('suppliers')">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                 style="display: none;"></div>
                            利用可能コード
                        </button>
                    </div>

                    {{-- 仕入先名--}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>仕入先名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="name" value="{{ old('name', $target_record_data['name'] ?? '') }}"
                                class="form-control input-name{{ $errors->has('name') ? ' is-invalid' : '' }}">

                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 仕入先名（かな） --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>仕入先名（カナ）</b>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="name_kana" value="{{ old('name_kana', $target_record_data['name_kana'] ?? '') }}"
                                class="form-control input-name-kana{{ $errors->has('name_kana') ? ' is-invalid' : '' }}">

                            @error('name_kana')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 郵便番号 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>郵便番号</b>
                        </label>
                        <div class="col-sm-1 col-5">
                            <input type="text" name="postal_code1" id="postal_code1"
                                   value="{{ old('postal_code1', $target_record_data['postal_code1'] ?? '') }}"
                                class="form-control input-postal-code1{{ $errors->has('postal_code1') ? ' is-invalid' : '' }}">

                            @error('postal_code1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        -
                        <div class="col-sm-1 col-5">
                            <input type="text" name="postal_code2" id="postal_code2"
                                   value="{{ old('postal_code2', $target_record_data['postal_code2'] ?? '') }}"
                                class="form-control input-postal-code2{{ $errors->has('postal_code2') ? ' is-invalid' : '' }}">

                            @error('postal_code2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button id="address-spinner" type="button" class="btn btn-primary"
                                onclick="searchAddress()">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                 style="display: none;"></div>
                            住所検索
                        </button>
                    </div>

                    {{-- 住所１ --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>住所１</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="address1" value="{{ old('address1', $target_record_data['address1'] ?? '') }}"
                                class="form-control input-address1{{ $errors->has('address1') ? ' is-invalid' : '' }}">

                            @error('address1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- 住所２ --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>住所２</b>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="address2" value="{{ old('address2', $target_record_data['address2'] ?? '') }}"
                                class="form-control input-address2{{ $errors->has('address2') ? ' is-invalid' : '' }}">

                            @error('address2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- 電話番号 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>電話番号</b>
                        </label>
                        <div class="col-sm-2">
                            <input type="text" name="tel_number" value="{{ old('tel_number', $target_record_data['tel_number'] ?? '') }}"
                                class="form-control input-tel-number{{ $errors->has('tel_number') ? ' is-invalid' : '' }}">

                            @error('tel_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- FAX番号 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>FAX番号</b>
                        </label>
                        <div class="col-sm-2">
                            <input type="text" name="fax_number" value="{{ old('fax_number', $target_record_data['fax_number'] ?? null) }}"
                                class="form-control input-fax-number{{ $errors->has('fax_number') ? ' is-invalid' : '' }}">

                            @error('fax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- メールアドレス --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>メールアドレス</b>
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
                        {{-- 税計算区分 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>税計算区分</b>
                        </label>
                        <div class="col-sm-4 col-5">
                            @foreach (($input_items['tax_calc_types'] ?? []) as $key => $val)
                                <div class="icheck-primary icheck-inline mr-2">
                                    <input type="radio" name="tax_calc_type_id" id="tax-calc-type-id-{{ $key }}" value="{{ $key }}"
                                        {{ old('tax_calc_type_id', $target_record_data['tax_calc_type_id'] ??
                                            config('consts.default.master.customers.tax_calc_type')) == $key ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                           for="tax-calc-type-id-{{ $key }}">{{ $val }}</label>
                                </div>
                            @endforeach
                            @error('tax_calc_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row my-3">
                        {{-- 税額端数処理 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>税額端数処理</b>
                        </label>
                        <div class="col-sm-4 col-5">
                            @foreach (($input_items['rounding_methods'] ?? []) as $key => $item)
                                <div class="icheck-primary icheck-inline mr-2">
                                    <input type="radio" name="tax_rounding_method_id" id="tax-rounding-method-id-{{ $key }}"
                                        value="{{ $item['id'] }}"
                                        {{ old('tax_rounding_method_id', $target_record_data['tax_rounding_method_id'] ?? config('consts.default.master.customers.tax_rounding_method')) == $item['id'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="tax-rounding-method-id-{{ $key }}">{{ $item['name'] }}</label>
                                </div>
                            @endforeach
                            @error('tax_rounding_method_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row my-3">
                        {{-- 仕入締日 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>仕入締日</b>
                        </label>
                        <div class="col-sm-2 col-5">
                            <div class="d-flex flex-row">
                                <select name="closing_date"
                                        class="custom-select input-closing-date clear-select
                                        @if($errors->has('closing_date')) is-invalid @endif">
                                    @foreach (($input_items['closing_date_list'] ?? []) as $key => $item)
                                        <option
                                            @if ($key == old('closing_date', $target_record_data['closing_date'] ?? null)) selected
                                            @endif
                                            value="{{ $key }}">
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                                <label class="col-form-label w-100 ml-1">日締</label>
                            </div>
                            @error('closing_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row my-3">
                        {{-- 開始買掛残高 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>開始買掛残高</b>
                        </label>
                        <div class="col-sm-2">
                            <input type="text" name="start_account_receivable_balance"
                                value="{{ old('start_account_receivable_balance', $target_record_data['start_account_receivable_balance'] ?? 0) }}"
                                class="form-control input-start-account-receivable-balance text-right{{ $errors->has("start_account_receivable_balance") ? ' is-invalid' : '' }}">

                            @error('start_account_receivable_balance')
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
                                      rows="4">{{ old('note', $target_record_data['note'] ?? '') }}</textarea>

                            @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="buttons-area text-center mt-4">
                        {{-- 一覧画面へ戻るボタン --}}
                        <a id="return" class="btn btn-primary back_active"
                        href="{{ session($session_master_key, route('master.suppliers.index')) }}">
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
                            {{ $next_btn_text }}
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
        <input type="hidden" name="api_get_next_usable_code_url" value="{{ route('api.common.get_next_usable_code') }}"
               class="hidden-api-get-next-usable-code-url">

    </div>

    <div>
        {{-- エラー情報 --}}
        <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">
        {{-- 新規/更新 --}}
        <input type="hidden" name="is-edit-route" value="{{ $is_edit_route }}" class="hidden-is-edit-route">
    </div>

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm" action="{{ route('master.suppliers.destroy', $target_record_data['id']) }}" method="POST">
        @method('DELETE')
        @csrf
        </form>
    @endif

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
    <script src="{{ mix('js/app/master/suppliers/create_edit.js') }}"></script>
@endsection
