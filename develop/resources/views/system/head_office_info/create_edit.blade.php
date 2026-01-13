{{-- 会社情報編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.system.menu.head_office_info');
    /** @see HeadOfficeInfoConst */
    $company_id = HeadOfficeInfoConst::COMPANY_ID;
    $next_url = route('system.head_office_info.update', $company_id);
    $next_btn_text = '更新';
    $method = 'PUT';
    $is_edit_route = true;

    /** @see HeadOfficeInfoConst */
    $maxlength_invoice_number = HeadOfficeInfoConst::INVOICE_NUMBER_MAX_LENGTH;   // インボイス番号最大桁数
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="input-area">

                <form name="editForm" id="editForm" action="{{ $next_url }}" method="POST" enctype="multipart/form-data">
                @method($method)
                @csrf
                    {{-- 会社名 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>会社名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="company_name" value="{{ old('company_name', $target_record_data['company_name'] ?? '' ) }}"
                                class="form-control input-company-name{{ $errors->has('company_name') ? ' is-invalid' : '' }}">

                            @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 代表者名 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>代表者名</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="representative_name" value="{{ old('representative_name', $target_record_data['representative_name'] ?? '' ) }}"
                                class="form-control input-representative-name{{ $errors->has('representative_name') ? ' is-invalid' : '' }}">

                            @error('representative_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 郵便番号 --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>郵便番号</b>
                        </label>
                        <div class="col-sm-1">

                            <input type="text" id="postal_code1" name="postal_code1" value="{{ old('postal_code1', $target_record_data['postal_code1'] ?? '' ) }}"
                                class="form-control input-postal-code1{{ $errors->has('postal_code1') ? ' is-invalid' : '' }}">

                            @error('postal_code1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        -
                        <div class="col-sm-1">

                            <input type="text" id="postal_code2" name="postal_code2" value="{{ old('postal_code2', $target_record_data['postal_code2'] ?? '' ) }}"
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

                            <input type="text" id="address1" name="address1" value="{{ old('address1', $target_record_data['address1'] ?? '' ) }}"
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

                            <input type="text" name="address2" value="{{ old('address2', $target_record_data['address2'] ?? '' ) }}"
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

                            <input type="text" name="tel_number" value="{{ old('tel_number', $target_record_data['tel_number'] ?? '' ) }}"
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

                            <input type="text" name="fax_number" value="{{ old('fax_number', $target_record_data['fax_number'] ?? '' ) }}"
                                class="form-control input-fax-number{{ $errors->has('fax_number') ? ' is-invalid' : '' }}">

                            @error('fax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- フリーダイヤル --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>フリーダイヤル</b>
                        </label>
                        <div class="col-sm-2">

                            <input type="text" name="tel_number2" value="{{ old('tel_number2', $target_record_data['tel_number2'] ?? '' ) }}"
                                   class="form-control input-fax-number2{{ $errors->has('tel_number2') ? ' is-invalid' : '' }}">

                            @error('tel_number2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- インボイス登録番号 --}}
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">
                            <b>インボイス登録番号</b>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="invoice_number" value="{{ old('invoice_number', $target_record_data['invoice_number'] ?? '' ) }}"
                                class="form-control input-invoice-number{{ $errors->has('invoice_number') ? ' is-invalid' : '' }}"
                                maxlength="{{ $maxlength_invoice_number }}">

                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- 社印画像 --}}
                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <b>社印画像</b>
                        </div>
                        <div class="col-sm-5 d-inline-flex">
                            {{-- 社印画像 --}}
                            <div class="col-sm-3 bg-dark border d-flex align-items-center justify-content-center">
                                <div class="img-thumbnail-container" style="@if(!$target_record_data['company_seal_image']) display:none; @endif">
                                    <img src="data:image/png;base64,{{ base64_encode($target_record_data['company_seal_image']) }}"
                                         class="d-block mx-auto img-thumbnail">
                                </div>
                                <div class="text-white img-placeholder" style="@if($target_record_data['company_seal_image']) display:none; @endif">
                                    データなし
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="col-sm-12">
                                    <div class="custom-file">

                                        <input type="file" name="company_seal_image" accept=".jpg, .png, .gif"
                                            class="custom-file-input input-company-seal-image{{ $errors->has('company_seal_image') ? ' is-invalid' : '' }}" >

                                        <label for="company_seal_image" id="company_seal_image_label"
                                            class="custom-file-label label-company-seal-image"
                                            data-browse="参照">ファイル選択</label>

                                        @error('company_seal_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- 社印画像ファイル名 --}}
                                <div class="col-sm-8 mt-2">
                                    <span class="img-file-name" style="@if(!$target_record_data['company_seal_image']) display:none; @endif">
                                        {{ $target_record_data['company_seal_image_file_name'] ?? null }}
                                    </span>
                                    <span class="img-file-name-placeholder" style="@if($target_record_data['company_seal_image']) display:none; @endif">
                                        未設定
                                    </span>
                                    @if($target_record_data['company_seal_image'])
                                        <button type="button" id="company_seal_image_del_btn"
                                                class="btn btn-sm btn-danger ml-2">削除
                                        </button>
                                        <input type="hidden" name="company_seal_image_del_flag" value="0">
                                        @error('company_seal_image_del_flag')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- 期首(会計開始月) --}}
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">
                            <b>期首(会計開始月)</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="col-sm-1">

                            <input type="text" name="fiscal_year" value="{{ old('fiscal_year', $target_record_data['fiscal_year'] ?? '' ) }}"
                                class="form-control input-fiscal-year-code1 {{ $errors->has('fiscal_year') ? 'is-invalid' : '' }}" />

                            @error('fiscal_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- 振込先１ --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>振込先１</b>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="bank_account1" value="{{ old('bank_account1', $target_record_data['bank_account1'] ?? '' ) }}"
                                   class="form-control input-bank-account1{{ $errors->has('bank_account1') ? ' is-invalid' : '' }}">

                            @error('bank_account1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- 振込先２ --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>振込先２</b>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="bank_account2" value="{{ old('bank_account2', $target_record_data['bank_account2'] ?? '' ) }}"
                                   class="form-control input-bank-account2{{ $errors->has('bank_account2') ? ' is-invalid' : '' }}">

                            @error('bank_account2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- 振込先１ --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>振込先３</b>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="bank_account3" value="{{ old('bank_account3', $target_record_data['bank_account3'] ?? '' ) }}"
                                   class="form-control input-bank-account3{{ $errors->has('bank_account1') ? ' is-invalid' : '' }}">

                            @error('bank_account3')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- 振込先１ --}}
                    <div class="form-group row my-3">
                        <label class="col-sm-2 col-form-label">
                            <b>振込先４</b>
                        </label>
                        <div class="col-sm-5">

                            <input type="text" name="bank_account4" value="{{ old('bank_account4', $target_record_data['bank_account4'] ?? '' ) }}"
                                   class="form-control input-bank-account4{{ $errors->has('bank_account4') ? ' is-invalid' : '' }}">

                            @error('bank_account4')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="buttons-area text-center mt-5">
                        {{-- 更新ボタン --}}

                        <button type="submit" class="btn btn-primary" style="display:none;">
                            {{ $next_btn_text }}
                        </button>

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
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Confirm Store Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-store')
        @slot('confirm_message', config('consts.message.common.confirm.update') )
        @slot('onclick_btn_ok', "store();return false;")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/master/create_edit.js') }}"></script>
    <script src="{{ mix('js/app/system/head_office_info/create_edit.js') }}"></script>
@endsection
