{{-- 納品先マスター登録・編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.recipients.create');
    $next_url = route('master.recipients.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    if ((Route::currentRouteName() === 'master.recipients.edit')) {
        $headline = config('consts.title.master.menu.recipients.edit');
        $next_url = route('master.recipients.update', $target_record_data['id']);
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
                    {{-- 支所名 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>支所名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <div class="d-md-inline-flex w-100">
                                <select name="branch_id"
                                        onchange="changeBranch();"
                                        class="custom-select input-branch-select w-md-50 mr-md-1 select2_search
                                        @if($errors->has('branch_id')) is-invalid @endif">
                                    <option value="">-----</option>
                                    @foreach (($input_items['branches'] ?? []) as $item)
                                        <option
                                            @if ($item['id'] == old('branch_id', $target_record_data['branch_id'] ?? null))
                                                selected
                                            @endif
                                            value="{{ $item['id'] }}"
                                            data-name="{{ $item['name'] }}">
                                            {{ StringHelper::getNameWithId($item->customer_name, $item['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 納品先名 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>納品先名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="recipient_name" value="{{ old('recipient_name', $target_record_data['name'] ?? '') }}"
                                class="form-control input-recipient-name{{ $errors->has('recipient_name') ? ' is-invalid' : '' }}">
                            @error('recipient_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 納品先名（かな） --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>納品先名（カナ）</b>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="name_kana" value="{{ old('name_kana', $target_record_data['name_kana'] ?? '') }}"
                                class="form-control input-name-kana{{ $errors->has('name_kana') ? ' is-invalid' : '' }}">

                            @error('name_kana')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="buttons-area text-center mt-4">
                        {{-- 一覧画面へ戻るボタン --}}
                        <a id="return" class="btn btn-primary back_active"
                        href="{{ session($session_master_key, route('master.recipients.index')) }}">
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
        {{-- エラー情報 --}}
        <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">
        {{-- 新規/更新 --}}
        <input type="hidden" name="is-edit-route" value="{{ $is_edit_route }}" class="hidden-is-edit-route">
    </div>

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm" action="{{ route('master.recipients.destroy', $target_record_data['id']) }}" method="POST">
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

    {{-- Search Branch Modal --}}
    @component('components.search_branch_modal')
        @slot('modal_id', 'search-branch')
        @slot('branches', $input_items['branches'])
        @slot('onclick_select_branch', "selectBranchSearchBranchModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/create_edit.js') }}"></script>
    <script src="{{ mix('js/app/master/recipients/create_edit.js') }}"></script>
@endsection
