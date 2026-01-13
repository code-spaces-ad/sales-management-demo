{{-- 得意先別単価マスター登録・編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.customer_price.create');
    $next_url = route('master.customer_price.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    if ((Route::currentRouteName() === 'master.customer_price.edit')) {
        $headline = config('consts.title.master.menu.customer_price.edit');
        $next_url = route('master.customer_price.update', $target_record_data['id']);
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
                            <input type="text" name="code"
                                   value="{{ old('code', $target_record_data['code_zerofill'] ?? '') }}"
                                   class="form-control input-code{{ $errors->has('code') ? ' is-invalid' : '' }}">

                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button id="code-spinner" type="button" class="btn btn-primary"
                                onclick="searchAvailableNumber('customer_price')">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                 style="display: none;"></div>
                            利用可能コード
                        </button>
                    </div>
                    {{-- 得意先 --}}
                    <div class="form-group row my-1 mt-3">
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

                    {{-- 商品 --}}
                    <div class="form-group row my-1 mt-3">
                        <label class="col-sm-2 col-form-label">
                            <b>商品</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">
                            <div class="d-md-inline-flex w-100">
                                {{-- 商品コード（セレクトボックス選択用） --}}
                                <input type="number"
                                       class="form-control input-product-code w-50 mr-md-2"
                                       id="product_code"
                                       oninput="inputCode(this);"
                                       onchange="changeProductCode(this);"
                                       placeholder="商品コード">
                                {{-- 商品 --}}
                                <select name="product_id"
                                        onchange="changeProduct();"
                                        class="custom-select input-product-select w-md-50 mr-md-1 select2_search @if($errors->has('product_id')) is-invalid @endif">
                                    <option value="">-----</option>
                                    @foreach (($input_items['products'] ?? []) as $item)
                                        <option
                                            @if ($item['id'] == old('product_id', $target_record_data['product_id'] ?? null)) selected
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
                            @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 通常税率_税込単価 --}}
                    <div class="form-group row my-3">
                        {{-- 通常税率_税込単価--}}
                        <label class="col-sm-2 col-form-label">
                            <b>通常税率_税込単価</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-2">
                            <input type="number" name="tax_included"
                                   value="{{ old('tax_included', $target_record_data['tax_included'] ?? '') }}"
                                   class="form-control input-tax-included{{ $errors->has('tax_included') ? ' is-invalid' : '' }}">

                            @error('tax_included')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 軽減税率_税込単価 --}}
                    <div class="form-group row my-3">
                        {{-- 軽減税率_税込単価 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>軽減税率_税込単価</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-2">
                            <input type="number" name="reduced_tax_included"
                                   value="{{ old('reduced_tax_included', $target_record_data['reduced_tax_included'] ?? '') }}"
                                   class="form-control input-reduced-tax-included{{ $errors->has("reduced_tax_included") ? ' is-invalid' : '' }}">

                            @error('reduced_tax_included')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 税抜単価 --}}
                    <div class="form-group row my-3">
                        {{-- 税抜単価 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>税抜単価</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-2">
                            <input type="number" name="unit_price"
                                   value="{{ old('unit_price', $target_record_data['unit_price'] ?? '') }}"
                                   class="form-control input-unit-price{{ $errors->has('unit_price') ? ' is-invalid' : '' }}">
                            @error('unit_price')
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
                       <textarea name="note"  rows="2" class="form-control input-note{{ $errors->has('note') ? ' is-invalid' : '' }}">{{ old('note', $target_record_data['note'] ?? '') }}</textarea>

                            @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="buttons-area text-center mt-4">
                        {{-- 一覧画面へ戻るボタン --}}
                        <a id="return" class="btn btn-primary back_active"
                           href="{{ session($session_master_key, route('master.products.index')) }}">
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

    @if ($is_edit_route)
        <form action="{{ route('master.customer_price.destroy', $target_record_data['id']) }}" name="deleteForm"
              id="deleteForm" method="POST">
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
    <script src="{{ mix('js/app/master/customer_price/create_edit.js') }}"></script>

@endsection
