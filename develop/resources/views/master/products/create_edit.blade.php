{{-- 商品マスター登録・編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.master.menu.products.create');
    $next_url = route('master.products.store');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    if ((Route::currentRouteName() === 'master.products.edit')) {
        $headline = config('consts.title.master.menu.products.edit');
        $next_url = route('master.products.update', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
    }

    $unit_id = $target_record_data['mProductUnit']['unit_id'] ?? null;  // 単位ID
    $unit_price_decimal_digit = $target_record_data['unit_price_decimal_digit'] ?? null;  // 単位小数桁数
    $quantity_decimal_digit = $target_record_data['quantity_decimal_digit'] ?? null;  // 数量小数桁数
    $maxlength_supplier_code = MasterSuppliersConst::CODE_MAX_LENGTH;   // 得意先コード最大桁数
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
                                onclick="searchAvailableNumber('products')">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                                 style="display: none;"></div>
                            利用可能コード
                        </button>
                    </div>

                    {{-- 商品名 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>商品名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" name="name" value="{{ old('name', $target_record_data['name'] ?? '') }}"
                                   class="form-control input-name{{ $errors->has('name') ? ' is-invalid' : '' }}">

                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 商品名（かな） --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>商品名（カナ）</b>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" name="name_kana"
                                   value="{{ old('name_kana', $target_record_data['name_kana'] ?? '') }}"
                                   class="form-control input-name-kana{{ $errors->has('name_kana') ? ' is-invalid' : '' }}">

                            @error('name_kana')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 相手先商品番号 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>相手先商品番号</b>
                        </label>
                        <div class="col-sm-3">
                            <input type="text" name="customer_product_code"
                                   value="{{ old('customer_product_code', $target_record_data['customer_product_code'] ?? '') }}"
                                   class="form-control input-customer-product-code{{ $errors->has('customer_product_code') ? ' is-invalid' : '' }}">

                            @error('customer_product_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- JANコード --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>JANコード</b>
                        </label>
                        <div class="col-sm-3">
                            <input type="text" name="jan_code"
                                   value="{{ old('jan_code', $target_record_data['jan_code'] ?? '') }}"
                                   class="form-control input-jan-code{{ $errors->has('jan_code') ? ' is-invalid' : '' }}">

                            @error('jan_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- カテゴリー --}}
                    @include('master.common.create_edit.category_select_list')

                    {{-- サブカテゴリー --}}
                    @include('master.common.create_edit.sub_category_select_list')

                    {{-- 経理コード --}}
                    @include('master.common.create_edit.accounting_code_select_list')

                    {{-- 商品_単価 --}}
                    <div class="form-group row my-3">
                        {{-- 単価 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>単価</b>
                        </label>
                        <div class="col-sm-2">
                            <input type="number" name="unit_price"
                                   value="{{ old('unit_price', $target_record_data['unit_price_floor'] ?? 0) }}"
                                   class="form-control{{ $errors->has('unit_price') ? ' is-invalid' : '' }}">

                            @error('unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 商品_仕入単価 --}}
                    <div class="form-group row my-3">
                        {{-- 仕入単価 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>仕入単価</b>
                        </label>
                        <div class="col-sm-2">
                            <input type="number" name="purchase_unit_price"
                                   value="{{ old('purchase_unit_price', $target_record_data['purchase_unit_price_floor'] ?? 0) }}"
                                   class="form-control input-unit-price_purchase{{ $errors->has("purchase_unit_price") ? ' is-invalid' : '' }}">

                            @error('purchase_unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row my-3">
                        {{-- 単価小数桁数 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>単価小数桁数</b>
                        </label>
                        <div class="col-sm-2">
                            <select name="unit_price_decimal_digit"
                                    onchange="changeUnitPrice();"
                                    class="custom-select input-unit-price-decimal-digit-select @if($errors->has('unit_price_decimal_digit')) is-invalid @endif">
                                @foreach (($input_items['unit_price_decimal_digits'] ?? []) as $item)
                                    <option
                                        @if ($item == old('unit_price_decimal_digit', $unit_price_decimal_digit)) selected
                                        @endif
                                        value="{{ $item }}">
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_price_decimal_digit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row my-3 d-none">
                        {{-- 数量小数桁数 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>数量小数桁数</b>
                        </label>
                        <div class="col-sm-2">
                            <select name="quantity_decimal_digit"
                                    class="custom-select input-quantity-decimal-digit-select @if($errors->has('quantity_decimal_digit')) is-invalid @endif">
                                @foreach (($input_items['quantity_decimal_digits'] ?? []) as $item)
                                    <option
                                        @if ($item == old('quantity_decimal_digit', $quantity_decimal_digit)) selected
                                        @endif
                                        value="{{ $item }}">
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            @error('quantity_decimal_digit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 税区分 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>税区分</b>
                        </label>
                        <div class="col-sm-6">
                            @foreach (($input_items['tax_types'] ?? []) as $key => $val)
                                <div class="icheck-primary icheck-inline mr-2">
                                    <input type="radio" name="tax_type_id" id="tax-type-id-{{ $key }}"
                                           value="{{ $key }}"
                                        {{ old('tax_type_id', $target_record_data['tax_type_id'] ?? config('consts.default.master.products.tax_type')) == $key ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tax-type-id-{{ $key }}">{{ $val }}</label>
                                </div>
                            @endforeach
                            @error('tax_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 消費税率 --}}
                    <div id="reduced_tax_flag" style="display: none;">
                        <div class="form-group row my-3">
                            <label class="col-sm-2 col-form-label">
                                <b>消費税率</b>
                            </label>
                            <div class="col-sm-6">
                                @foreach (($input_items['tax_rate_types'] ?? []) as $key => $val)
                                    @php
                                        $rate = null;
                                        $readonly = false;
                                        if ($key === \App\Enums\ReducedTaxFlagType::NOT_REDUCED_TAX) {
                                            $rate = $input_items['default_tax_list']['normal_tax_rate'] ?? null;
                                        } elseif ($key === \App\Enums\ReducedTaxFlagType::REDUCED_TAX) {
                                            $rate = $input_items['default_tax_list']['reduced_tax_rate'] ?? null;
                                            $readonly = is_null($rate); // rate が null のとき readonly 扱い
                                        }
                                    @endphp

                                    <div class="icheck-primary icheck-inline mr-2">
                                        <input type="radio" name="reduced_tax_flag" id="reduced-tax-flag-{{ $key }}"
                                               value="{{ $key }}"
                                               data-tax-rate="{{ $rate }}"
                                               class="{{ $readonly ? 'readonly-radio' : '' }}"
                                               {{ old('reduced_tax_flag', $target_record_data['reduced_tax_flag'] ?? config('consts.default.master.products.reduced_tax_flag')) == $key ? 'checked' : '' }}>
                                        <label class="form-check-label {{ $readonly ? 'readonly-radio-label' : '' }}" for="reduced-tax-flag-{{ $key }}">{{ $val }}</label>
                                    </div>
                                @endforeach
                                @error('reduced_tax_flag')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 数量端数処理 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>数量端数処理</b>
                        </label>
                        <div class="col-sm-6">
                            @foreach (($input_items['rounding_methods'] ?? []) as $key => $val)
                                <div class="icheck-primary icheck-inline mr-2">
                                    <input type="radio" name="quantity_rounding_method_id"
                                           id="quantity-rounding-method-id-{{ $key }}"
                                           value="{{ $val['id'] }}"
                                        {{ old('quantity_rounding_method_id', $target_record_data['quantity_rounding_method_id'] ?? config('consts.default.master.products.quantity_rounding_method')) == $val['id'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                           for="quantity-rounding-method-id-{{ $key }}">{{ $val['name'] }}</label>
                                </div>
                            @endforeach
                            @error('quantity_rounding_method_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 単価端数処理 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>単価端数処理</b>
                        </label>
                        <div class="col-sm-6">
                            @foreach (($input_items['rounding_methods'] ?? []) as $key => $val)
                                <div class="icheck-primary icheck-inline mr-2">
                                    <input type="radio" name="amount_rounding_method_id"
                                           id="amount-rounding-method-id-{{ $key }}"
                                           value="{{ $val['id'] }}"
                                        {{ old('amount_rounding_method_id', $target_record_data['amount_rounding_method_id'] ?? config('consts.default.master.products.amount_rounding_method')) == $val['id'] ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                           for="amount-rounding-method-id-{{ $key }}">{{ $val['name'] }}</label>
                                </div>
                            @endforeach
                            @error('amount_rounding_method_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 仕入先 --}}
                    @include('master.common.create_edit.supplier_select_list')

                    {{-- 備考 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>備考</b>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" name="note"
                                   value="{{ old('note', $target_record_data['note'] ?? '') }}"
                                   class="form-control input-note{{ $errors->has('note') ? ' is-invalid' : '' }}">

                            @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 仕様 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>仕様</b>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" name="specification"
                                   value="{{ old('specification', $target_record_data['specification'] ?? '') }}"
                                   class="form-control input-specification{{ $errors->has('specification') ? ' is-invalid' : '' }}">

                            @error('specification')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 種別 --}}
                    @include('master.common.create_edit.kind_select_list')

                    {{-- 管理部署 --}}
                    @include('master.common.create_edit.section_select_list')

                    {{-- 単重 --}}
                    <div class="form-group row my-3">
                        {{-- 単重 --}}
                        <label class="col-sm-2 col-form-label">
                            <b>単重</b>
                        </label>
                        <div class="col-sm-2">
                            <input type="number" name="purchase_unit_weight"
                                   value="{{ old('purchase_unit_weight', $target_record_data['purchase_unit_weight'] ?? 0) }}"
                                   class="form-control{{ $errors->has('purchase_unit_weight') ? ' is-invalid' : '' }}">

                            @error('purchase_unit_weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 分類１ --}}
                    @include('master.common.create_edit.classification1_select_list')

                    {{-- 分類２ --}}
                    @include('master.common.create_edit.classification2_select_list')

                    {{-- 商品区分 --}}
                    @include('master.common.create_edit.product_status_select_list')

                    {{-- 棚番 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>棚番</b>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" name="rack_address"
                                   value="{{ old('rack_address', $target_record_data['rack_address'] ?? '') }}"
                                   class="form-control input-rack-address{{ $errors->has('rack_address') ? ' is-invalid' : '' }}">

                            @error('rack_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- TODO: 詳細エリアの実装のみ --}}
                    <div style="display: none;">
                        <p class="toggle-event" data-id="detail_area">
                            <a class="btn btn-primary">
                                詳細表示
                            </a>
                        </p>
                    </div>
                    <div id="detail_area" style="display: none;">
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
                        <input type="submit" id="btn_submit" value="{{ $next_btn_text }}" class="btn btn-primary"
                               style="display:none;">

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
        <form action="{{ route('master.products.destroy', $target_record_data['id']) }}" name="deleteForm"
              id="deleteForm" method="POST">
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
    <script src="{{ mix('js/app/master/products/create_edit.js') }}"></script>

@endsection
