{{-- 支所マスター登録・編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.branches.create');
    $next_url = route('master.branches.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    if ((Route::currentRouteName() === 'master.branches.edit')) {
        $headline = config('consts.title.master.menu.branches.edit');
        $next_url = route('master.branches.update', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
    }

    /** @see MasterCustomersConst */
    $maxlength_customer_code = MasterCustomersConst::CODE_MAX_LENGTH;   // 得意先コード最大桁数
    $min_customer_code = MasterCustomersConst::CODE_MIN_VALUE;   // 得意先コード最小値
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
                    {{-- 得意先 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>得意先</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <div class="d-md-inline-flex w-100">
                                {{-- 得意先コード（セレクトボックス選択用） --}}
                                <input type="number"
                                    class="form-control input-customer-code w-50 mr-md-2"
                                    id="customer_code"
                                    oninput="inputCode(this);"
                                    onchange="changeCustomerCode(this);"
                                    maxlength="{{ $maxlength_customer_code }}"
                                    min="{{ $min_customer_code }}"
                                    placeholder="得意先コード">
                                {{-- 得意先 --}}
                                <select name="customer_id"
                                        onchange="changeCustomer();"
                                        class="custom-select input-customer-select w-md-50 mr-md-1 select2_search @if($errors->has('customer_id')) is-invalid @endif">
                                    <option value="">-----</option>
                                    @foreach (($input_items['customers'] ?? []) as $item)
                                        <option
                                            @if ($item['id'] == old('customer_id', $target_record_data['customer_id'] ?? null)) selected
                                            @endif
                                            value="{{ $item['id'] }}"
                                            data-code="{{ $item['code'] }}"
                                            data-name="{{ $item['name'] }}"
                                            data-name-kana="{{ $item['name_kana'] }}">
                                            {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 支所名 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>支所名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="branch_name"
                                value="{{ old('branch_name', $target_record_data['name'] ?? '') }}"
                                class="form-control input-branch-name{{ $errors->has('branch_name') ? ' is-invalid' : '' }}">

                            @error('branch_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 支所名（かな） --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>支所名（カナ）</b>
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

                    {{-- 支所名略称 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>支所名略称</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="mnemonic_name"
                                value="{{ old('mnemonic_name', $target_record_data['mnemonic_name'] ?? '') }}"
                                class="form-control input-mnemonic_name{{ $errors->has('mnemonic_name') ? ' is-invalid' : '' }}">
                            @error('mnemonic_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="buttons-area text-center mt-4">
                        {{-- 一覧画面へ戻るボタン --}}
                        <a id="return" class="btn btn-primary back_active"
                        href="{{ session($session_master_key, route('master.branches.index')) }}">
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
                        <input type="submit" id="btn_submit" value="{{ $next_btn_text }}"
                            class="btn btn-primary" style="display:none;">

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
        {{-- エラー情報 --}}
        <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">
        {{-- 新規/更新 --}}
        <input type="hidden" name="is-edit-route" value="{{ $is_edit_route }}" class="hidden-is-edit-route">
    </div>

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm" action="{{ route('master.branches.destroy', $target_record_data['id']) }}" method="POST">
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

    {{-- Search Custmoer Modal --}}
    @component('components.search_customer_modal')
        @slot('modal_id', 'search-customer')
        @slot('customers', $input_items['customers'])
        @slot('onclick_select_customer', "selectCustomerSearchCustomerModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/create_edit.js') }}"></script>
    <script src="{{ mix('js/app/master/branches/create_edit.js') }}"></script>

@endsection
