{{-- 支払伝票入力画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.trading.menu.payment.create');
    $next_url = route('trading.payments.store');
    $api_url = route('api.purchase_closing.is_closing');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    $is_closing = false;
    $order_number = '新規';
    if (Route::currentRouteName() === 'trading.payments.edit') {
        $headline = config('consts.title.trading.menu.payment.edit');
        $next_url = route('trading.payments.update', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
        $is_closing = !is_null($target_record_data['closing_at']);
        $order_number = $target_record_data['order_number_zerofill'];
    }

    $default_transaction_id = config('consts.default.common.transaction_type_id', 2);    // デフォルト取引種別ID
    $default_order_date = \Carbon\Carbon::today()->toDateString();  // デフォルト伝票日付
    /** @see MasterSuppliersConst */
    $maxlength_supplier_code = MasterSuppliersConst::CODE_MAX_LENGTH;   // 仕入先コード最大桁数
    $min_supplier_code = MasterSuppliersConst::CODE_MIN_VALUE;   // 仕入先コード最小値

    // デフォルトMAX日付
    $default_max_date = config('consts.default.common.default_max_date');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')

    <form name="editForm" id="editForm" action="{{ $next_url }}" method="POST" onsubmit="return editFormSubmit();">
    @method($method)
    @csrf

    <div class="row">
        @if ($is_closing)
            <div class="row col-md-12 pb-1 pt-1 mb-2 btn-success" style="font-size: 1.5em;">締処理済みデータ</div>
        @else
            <div class="row col-md-12 pb-1 pt-1 mb-2 btn-warning" style="font-size: 1.5em;display: none;"
                 data-target="#order-label">
                現在入力中の仕入先・伝票日付は締データが存在するため登録できません。
            </div>
        @endif

        <div class="col-12 px-0 px-md-4 pb-md-2">
            <div class="card">
                <div class="card-body">
                    @if (Route::currentRouteName() === 'trading.payments.edit')
                        <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                            {{-- 伝票番号 --}}
                            <div class="form-group d-md-inline-flex col-md-6 my-1">
                                <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                    <b>伝票番号</b>
                                </label>
                                <div class="col-md-4 pl-0">
                                    <input type="text" class="form-control input-order-number" id="order_number"
                                           value="{{ $order_number }}" disabled>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- 伝票種別 --}}
                        <div class="form-group d-md-inline-flex col-md-6 my-1">
                            <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                <b>伝票種別</b>
                                <span class="badge badge-danger">必須</span>
                            </label>
                            <div class="col-md-4 pl-0">
                                <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                    <select name="transaction_type_id"
                                            class="custom-select input-transaction-type-select
                                            @if($errors->has('transaction_type_id')) is-invalid @endif"
                                            onchange="checkPurchaseClosed(true);">
                                        @foreach (($input_items['transaction_types'] ?? []) as $val => $name)
                                            <option
                                                @if ($val == old('transaction_type_id', $target_record_data['transaction_type_id'] ?? $default_transaction_id))
                                                selected
                                                @endif
                                                value="{{ $val }}">
                                                {{ StringHelper::getNameWithId($val, $name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('transaction_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- 伝票日付 --}}
                        <div class="form-group d-md-inline-flex col-md-6 my-1">
                            <label class="col-form-label col-md-3 pl-0">
                                <b>伝票日付</b>
                                <span class="badge badge-danger">必須</span>
                            </label>
                            <div class="col-md-4 pl-0">

                                <input type="date" name="order_date" id="order_date"
                                       value="{{ old('order_date', $target_record_data['order_date'] ?? $default_order_date) }}"
                                       class="form-control input-order-date{{ $errors->has('order_date') ? ' is-invalid' : '' }}" max="{{ $default_max_date }}"
                                       onchange="changeOrderDate();checkPurchaseClosed(true);" >

                                @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- 仕入先 --}}
                        <div class="form-group d-md-inline-flex col-md-6 my-1">
                            <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                <b>仕入先</b>
                                <span class="badge badge-danger">必須</span>
                            </label>
                            <div class="flex-md-column col-md-9 pr-md-0 pl-0">
                                <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                    {{-- 仕入先コード --}}
                                    <input type="number"
                                           class="form-control h-75 input-supplier-code col-5 col-md-4 mr-md-1"
                                           id="supplier_code" oninput="inputCode(this);"
                                           onchange="changeSupplierCodeCreateEdit(this);checkPurchaseClosed(true);"
                                           maxlength="{{ $maxlength_supplier_code }}"
                                           min="{{ $min_supplier_code }}"
                                           value="{{ $target_record_data->supplier_code_zero_fill }}"
                                           placeholder="仕入先コード">
                                    {{-- 仕入先 --}}
                                    <select name="supplier_id"
                                            onchange="changeSupplierCreateEdit();checkPurchaseClosed(true);"
                                            id="mySelect2"
                                            class="custom-select input-supplier-select col-9 px-0 mr-md-1 select2_search d-none
                                            @if($errors->has('supplier_id')) is-invalid @endif">
                                        <option value="">-----</option>
                                        @foreach (($input_items['suppliers'] ?? []) as $item)
                                            {{--// 仕入は3（明細単位）/ 3(四捨五入)固定 --}}
                                            <option
                                                @if ($item['id'] == old('supplier_id', $target_record_data['supplier_id'] ?? null))
                                                selected
                                                @endif
                                                value="{{ $item['id'] }}"
                                                data-code="{{ $item['code'] }}"
                                                data-name="{{ $item['name'] }}"
                                                data-name-kana="{{ $item['name_kana'] }}"
                                                data-payment-balance="{{ $item['payment_balance'] }}"
                                                data-tax-calc-type="{{ $item['tax_calc_type_id'] }}"
                                                data-tax-rounding-method="{{ $item['tax_rounding_method_id'] }}">
                                                {{ $item['name'] }} ({{ \App\Enums\TaxCalcType::asSelectArray()[$item['tax_calc_type_id']] }} - {{ \App\Enums\RoundingMethodType::asSelectArray()[$item['tax_rounding_method_id']] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        @php
                            $required_department = true;
                        @endphp
                        <div class="form-group d-md-inline-flex col-md-6 my-1">
                            <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                <b>部門</b>
                                @if(isset($required_department))
                                    <span class="badge badge-danger">必須</span>
                                @endif
                            </label>
                            <div class="flex-md-column col-md-9 pr-md-0 pl-0">
                                <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                    {{-- 部門コード --}}
                                    <input type="number"
                                           class="form-control input-department-code h-75 col-5 col-md-4 mr-md-1"
                                           id="department_id_code" oninput="inputCode(this);"
                                           onchange="changeDepartmentCode(this);"
                                           placeholder="部門コード">
                                    {{-- 部門 --}}
                                    <select name="department_id" onchange="changeDepartment();"
                                            class="custom-select input-department-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                                            @if($errors->has('department_id')) is-invalid @endif">
                                        @if(!isset($required_department))
                                            <option value="">-----</option>
                                        @endif
                                        @foreach (($input_items['departments'] ?? []) as $item)
                                            <option
                                                @if ($item['id'] == old('department_id', $target_record_data['department_id'] ?? null))
                                                selected
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
                                @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        <div class="form-group d-md-inline-flex col-md-6 my-1">
                            @php
                                $required_office_facility = true;
                            @endphp
                            <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                <b>事業所</b>
                                @if(isset($required_office_facility))
                                    <span class="badge badge-danger">必須</span>
                                @endif
                            </label>
                            <div class="flex-md-column col-md-9 pr-md-0 pl-0">
                                <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                    {{-- 事業所コード --}}
                                    <input type="number"
                                           class="form-control input-office-facility-code h-75 col-5 col-md-4 mr-md-1 clear-value"
                                           id="office_facilities_id_code" oninput="inputCode(this);"
                                           onchange="changeOfficeFacilityCode(this);"
                                           placeholder="事業所コード">
                                    {{-- 事業所 --}}
                                    <select name="office_facilities_id" onchange="changeOfficeFacility()"
                                            class="office-facility-select input-office-facility-select col-9 px-0 mr-md-1 select2_search d-none clear-select
                                            @if($errors->has('office_facilities_id')) is-invalid @endif">
                                        @if(!isset($required_office_facility))
                                            <option value="">-----</option>
                                        @endif
                                        @foreach (($input_items['office_facilities'] ?? []) as $item)
                                            <option
                                                @if ($item['id'] == old('office_facilities_id', $target_record_data['office_facilities_id'] ?? null))
                                                selected
                                                @endif
                                                value="{{ $item['id'] }}"
                                                data-code="{{ $item['code'] }}"
                                                data-name="{{ $item['name'] }}"
                                                data-name-kana="{{ $item['name_kana'] }}"
                                                data-department-id="{{ $item['department_id'] }}">
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('office_facilities_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- 備考 --}}
                        <div class="form-group d-md-inline-flex col-md-6 my-1">
                            <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                <b>備考</b>
                            </label>
                            <div class="col-md-9 pl-0">
                                <input type="text" name="note" id="note" value="{{ old('note', $target_record_data['note'] ?? null) }}"
                                       class="form-control input-note{{ $errors->has('note') ? ' is-invalid' : '' }}" >
                                @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- メモ --}}
                        @include('common.create_edit.memo')
                    </div>

                    @if (Route::currentRouteName() === 'trading.payments.edit')
                        <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                            <div class="col-sm-6 mt-2">
                                <div class="table-responsive table-fixed-sm col-sm-12 @if(!$is_edit_route) invisible @endif">
                                    <table class="table table-bordered table-responsive-org table-sm">
                                        <thead class="thead-light text-center">
                                        <tr>
                                            <th style="width: 25%; font-size: 0.9rem;">更新者</th>
                                            <th style="width: 25%; font-size: 0.9rem;">更新日</th>
                                            <th style="width: 25%; font-size: 0.9rem;">登録者</th>
                                            <th style="width: 25%; font-size: 0.9rem;">登録日</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td class="text-left" data-title="更新者" style="font-size: 0.8rem">
                                                {{ $target_record_data->updated_name }}
                                            </td>
                                            <td class="text-center" data-title="更新日" style="font-size: 0.9rem">
                                                {{ $target_record_data->updated_at_slash }}
                                            </td>
                                            <td class="text-left" data-title="更新者" style="font-size: 0.8rem">
                                                {{ $target_record_data->creator_name }}
                                            </td>
                                            <td class="text-center" data-title="更新日" style="font-size: 0.9rem">
                                                {{ $target_record_data->created_at_slash }}
                                            </td>
                                        <tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <div class="col-6 px-0 px-md-4 pb-md-2">
            <div class="card">
                <div class="card-header d-md-inline-flex">
                    <b>支払額内訳</b>
                </div>
                <div class="card-body">
                    <div class="row col-md-12">
                        <div class="d-md-inline-flex col-sm-12">
                            {{-- ※スマホ時は非表示 --}}
                            <label class="d-none d-sm-block col-sm-3 pl-0 col-form-label col-form-label-sm"
                                   style="font-size: 1.02em">
                            </label>
                            {{-- ※スマホ時は非表示 --}}
                            <label class="d-none d-sm-block col-sm-4 col-form-label">
                                <b>金額</b>
                            </label>
                            {{-- ※スマホ時は非表示 --}}
                            <label class="d-none d-sm-block col-sm-5 col-form-label">
                                <b>備考</b>
                            </label>
                        </div>
                    </div>
                    <div class="row col-md-12">
                        <div class="row col-md-12">
                            <div class="d-md-inline-flex col-sm-12 mb-2">
                                {{-- 現金 --}}
                                <label class="col-sm-3 col-form-label">
                                    <b>現金</b>
                                </label>
                                {{-- 金額_現金 --}}
                                <div class="col-sm-4 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        金額：
                                    </label>

                                    <input type="text" name="amount_cash" id="amount_cash"
                                           value="{{ old('amount_cash', $target_record_data['paymentDetail']['amount_cash_comma'] ?? 0) }}"
                                           class="form-control form-control-sm input-amount-cash text-right {{ $errors->has('amount_cash') ? 'is-invalid' : '' }}">

                                    @error('amount_cash')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- 備考_現金 --}}
                                <div class="col-sm-5 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        備考：
                                    </label>

                                    <input type="text" name="note_cash" id="note_cash"
                                           value="{{ old('note_cash', $target_record_data['paymentDetail']['note_cash'] ?? null) }}"
                                           class="form-control form-control-sm input-note-cash {{ $errors->has('note_cash') ? ' is-invalid' : '' }}">

                                    @error('note_cash')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row col-md-12">
                            <div class="d-md-inline-flex col-sm-12 mb-2">
                                {{-- 小切手 --}}
                                <label class="col-sm-3 col-form-label">
                                    <b>小切手</b>
                                </label>
                                {{-- 金額_小切手 --}}
                                <div class="col-sm-4 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        金額：
                                    </label>

                                    <input type="text" name="amount_check" id="amount_check"
                                           value="{{ old('amount_check', $target_record_data['paymentDetail']['amount_check_comma'] ?? 0) }}"
                                           class="form-control form-control-sm input-amount-check text-right {{ $errors->has('amount_check') ? 'is-invalid' : '' }}">

                                    @error('amount_check')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- 備考_小切手 --}}
                                <div class="col-sm-5 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        備考：
                                    </label>

                                    <input type="text" name="note_check" id="note_check"
                                           value="{{ old('note_check', $target_record_data['paymentDetail']['note_check'] ?? null) }}"
                                           class="form-control form-control-sm input-note-check {{ $errors->has('note_check') ? 'is-invalid' : '' }}">

                                    @error('note_check')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row col-md-12">
                            <div class="d-md-inline-flex col-sm-12 mb-2">
                                {{-- 振込 --}}
                                <label class="col-sm-3 col-form-label">
                                    <b>振込</b>
                                </label>
                                {{-- 金額_振込 --}}
                                <div class="col-sm-4 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        金額：
                                    </label>

                                    <input type="text" name="amount_transfer" id="amount_transfer"
                                           value="{{ old('amount_transfer', $target_record_data['paymentDetail']['amount_transfer_comma'] ?? 0) }}"
                                           class="form-control form-control-sm input-amount-transfer text-right {{ $errors->has('amount_transfer') ? ' is-invalid' : '' }}">

                                    @error('amount_transfer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- 備考_振込 --}}
                                <div class="col-sm-5 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        備考：
                                    </label>

                                    <input type="text" name="note_transfer" id="note_transfer"
                                           value="{{ old('note_transfer', $target_record_data['paymentDetail']['note_transfer'] ?? null) }}"
                                           class="form-control form-control-sm input-note-transfer {{ $errors->has('note_transfer') ? ' is-invalid' : '' }}">

                                    @error('note_transfer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row col-md-12">
                            <div class="d-md-inline-flex col-sm-12">
                                {{-- 振込 --}}
                                <label class="col-sm-3 col-form-label">
                                    <b>手形</b>
                                </label>
                                {{-- 金額_手形 --}}
                                <div class="col-sm-4 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        金額：
                                    </label>

                                    <input type="text" name="amount_bill" id="amount_bill"
                                           value="{{ old('amount_bill', $target_record_data['paymentDetail']['amount_bill_comma'] ?? 0) }}"
                                           class="form-control form-control-sm input-amount-bill text-right {{ $errors->has('amount_bill') ? ' is-invalid' : '' }}">

                                    @error('amount_bill')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- 備考_手形 --}}
                                <div class="col-sm-5 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        備考：
                                    </label>

                                    <input type="text" name="note_bill" id="note_bill"
                                           value="{{ old('note_bill', $target_record_data['paymentDetail']['note_bill'] ?? null) }}"
                                           class="form-control form-control-sm input-note-bill {{ $errors->has('note_bill') ? ' is-invalid' : '' }}">

                                    @error('note_bill')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row col-md-12">
                            <div class="d-md-inline-flex col-sm-12">
                                {{-- 手形期日 --}}
                                <label class="col-sm-3 col-form-label text-right">
                                    <b>手形期日</b>
                                </label>
                                <div class="col-sm-4">

                                    <input type="date" name="bill_date" id="bill_date"
                                           value="{{ old('bill_date', $target_record_data['paymentBill']['bill_date'] ?? null) }}"
                                           class="form-control form-control-sm input-bill-date {{ $errors->has('bill_date') ? 'is-invalid' : '' }}" max="{{ $default_max_date }}">

                                    @error('bill_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-md-inline-flex col-sm-12 mb-2">
                                {{-- 手形番号 --}}
                                <label class="col-sm-3 col-form-label text-right">
                                    <b>手形番号</b>
                                </label>
                                <div class="col-sm-4">

                                    <input type="text" name="bill_number" id="bill_number"
                                           value="{{ old('bill_number', $target_record_data['paymentBill']['bill_number'] ?? null) }}"
                                           class="form-control form-control-sm input-bill-number text-left {{ $errors->has('bill_number') ? ' is-invalid' : '' }}">

                                    @error('bill_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row col-md-12">
                            <div class="d-md-inline-flex col-sm-12 mb-2 order-2 order-md-1">
                                {{-- 相殺 --}}
                                <label class="col-sm-3 col-form-label">
                                    <b>相殺</b>
                                </label>
                                {{-- 金額_相殺 --}}
                                <div class="col-sm-4 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        金額：
                                    </label>

                                    <input type="text" name="amount_offset" id="amount_offset"
                                           value="{{ old('amount_offset', $target_record_data['paymentDetail']['amount_offset_comma'] ?? 0) }}"
                                           class="form-control form-control-sm input-amount-offset text-right {{ $errors->has('amount_bill') ? ' is-invalid' : '' }}">

                                    @error('amount_offset')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- 備考_相殺 --}}
                                <div class="col-sm-5 d-inline-flex">
                                    {{-- ※スマホ時は表示 --}}
                                    <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                        備考：
                                    </label>

                                    <input type="text" name="note_offset" id="note_offset"
                                           value="{{ old('note_offset', $target_record_data['paymentDetail']['note_offset'] ?? null) }}"
                                           class="form-control form-control-sm input-note-offset {{ $errors->has('note_offset') ? ' is-invalid' : '' }}">

                                    @error('note_offset')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-header d-md-inline-flex">
                    <b>調整額内訳</b>
                </div>
                <div class="card-body">
                    <div class="row col-md-12">
                        <div class="d-md-inline-flex col-sm-12">
                            {{-- ※スマホ時は非表示 --}}
                            <label class="d-none d-sm-block col-sm-3 pl-0 col-form-label col-form-label-sm"
                                   style="font-size: 1.02em">
                            </label>
                            {{-- ※スマホ時は非表示 --}}
                            <label class="d-none d-sm-block col-sm-4 col-form-label">
                                <b>金額</b>
                            </label>
                            {{-- ※スマホ時は非表示 --}}
                            <label class="d-none d-sm-block col-sm-7 col-form-label">
                                <b>備考</b>
                            </label>
                        </div>
                    </div>
                    <div class="row col-md-12">
                        <div class="d-md-inline-flex col-sm-12 mb-2">
                            {{-- 値引 --}}
                            <label class="col-sm-3 col-form-label">
                                <b>値引</b>
                            </label>
                            {{-- 金額_値引 --}}
                            <div class="col-sm-4 d-inline-flex">
                                {{-- ※スマホ時は表示 --}}
                                <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                    金額：
                                </label>

                                <input type="text" name="amount_discount" id="amount_discount"
                                       value="{{ old('amount_discount', $target_record_data['paymentDetail']['amount_discount_comma'] ?? 0) }}"
                                       class="form-control form-control-sm input-amount-discount text-right {{ $errors->has('amount_bill') ? ' is-invalid' : '' }}">

                                @error('amount_discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 備考_値引 --}}
                            <div class="col-sm-5 d-inline-flex">
                                {{-- ※スマホ時は表示 --}}
                                <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                    備考：
                                </label>

                                <input type="text" name="note_discount" id="note_discount"
                                       value="{{ old('note_discount', $target_record_data['paymentDetail']['note_discount'] ?? null) }}"
                                       class="form-control form-control-sm input-note-discount {{ $errors->has('note_discount') ? ' is-invalid' : '' }}">

                                @error('note_discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row col-md-12">
                        <div class="d-md-inline-flex col-sm-12 mb-2">
                            {{-- 手数料 --}}
                            <label class="col-sm-3 col-form-label">
                                <b>手数料</b>
                            </label>
                            {{-- 金額_手数料 --}}
                            <div class="col-sm-4 d-inline-flex">
                                {{-- ※スマホ時は表示 --}}
                                <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                    金額：
                                </label>

                                <input type="text" name="amount_fee" id="amount_fee"
                                       value="{{ old('amount_fee', $target_record_data['paymentDetail']['amount_fee_comma'] ?? 0) }}"
                                       class="form-control form-control-sm input-amount-fee text-right {{ $errors->has('amount_fee') ? ' is-invalid' : '' }}">

                                @error('amount_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 備考_手数料 --}}
                            <div class="col-sm-5 d-inline-flex">
                                {{-- ※スマホ時は表示 --}}
                                <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                    備考：
                                </label>

                                <input type="text" name="note_fee" id="note_fee"
                                       value="{{ old('note_fee', $target_record_data['paymentDetail']['note_fee'] ?? null) }}"
                                       class="form-control form-control-sm input-note-fee {{ $errors->has('note_fee') ? ' is-invalid' : '' }}">

                                @error('note_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row col-md-12">
                        <div class="d-md-inline-flex col-sm-12 mb-2">
                            {{-- その他 --}}
                            <label class="col-sm-3 col-form-label">
                                <b>その他</b>
                            </label>
                            {{-- 金額_その他 --}}
                            <div class="col-sm-4 d-inline-flex">
                                {{-- ※スマホ時は表示 --}}
                                <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                    金額：
                                </label>

                                <input type="text" name="amount_other" id="amount_other"
                                       value="{{ old('amount_other', $target_record_data['paymentDetail']['amount_other_comma'] ?? 0) }}"
                                       class="form-control form-control-sm input-amount-other text-right {{ $errors->has('amount_other') ? ' is-invalid' : '' }}">

                                @error('amount_other')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 備考_その他 --}}
                            <div class="col-sm-5 d-inline-flex">
                                {{-- ※スマホ時は表示 --}}
                                <label class="d-sm-none col-form-label col-form-label-sm text-nowrap">
                                    備考：
                                </label>

                                <input type="text" name="note_other" id="note_other"
                                       value="{{ old('note_other', $target_record_data['paymentDetail']['note_other'] ?? null) }}"
                                       class="form-control form-control-sm input-note-other {{ $errors->has('note_other') ? ' is-invalid' : '' }}">

                                @error('note_other')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 pl-0">
            <div class="row col-md-12">
                <div class="d-md-inline-flex col-sm-12 mb-2 pt-4">
                    {{-- 請求残高 --}}
                    <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size: 1.5em">
                        <b>請求残高</b>
                    </label>
                    {{-- 請求残高 --}}
                    <div class="col-sm-5">

                        <input type="text" name="payment-balance" id="payment-balance" style="font-size: 1.5em" value="-"
                            class="form-control form-control-sm input-supplier-payment-balance text-right {{ $errors->has('payment-balance') ? 'is-invalid' : '' }}"
                            disabled>

                        @error('payment-balance')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-md-inline-flex col-sm-12">
                    <b>
                        <label class="col-sm-12 offset-5 p-0 col-form-label-sm input-label-closing-info"
                               style="font-size: 1.1em"></label>
                    </b>
                </div>

                <div class="d-md-inline-flex col-sm-12 mb-2">
                    {{-- 支払額小計 --}}
                    <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size: 1.5em">
                        <b>支払額小計</b>
                    </label>
                    {{-- 支払額小計 --}}
                    <div class="col-sm-5">

                        <input type="text" name="payment-subtotal" id="payment-subtotal" style="font-size: 1.5em" value="0"
                            class="form-control form-control-sm input-payment-subtotal text-right {{ $errors->has('payment-subtotal') ? 'is-invalid' : '' }}"
                            disabled>

                        @error('payment-subtotal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-md-inline-flex col-sm-12 mb-2">
                    {{-- 調整額小計 --}}
                    <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size: 1.5em">
                        <b>調整額小計</b>
                    </label>
                    {{-- 調整額小計 --}}
                    <div class="col-sm-5">

                        <input type="text" name="adjust-subtotal" id="adjust-subtotal" style="font-size: 1.5em" value="0"
                            class="form-control form-control-sm input-adjust-subtotal text-right {{ $errors->has('adjust-subtotal') ? ' is-invalid' : '' }}"
                            disabled>

                        @error('adjust-subtotal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-md-inline-flex col-sm-12 p-2 border border-primary"
                     style="border-width: medium!important;">
                    {{-- 今回支払額合計 --}}
                    <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size: 1.5em">
                        <b>今回支払額合計</b>
                    </label>
                    <div class="col-sm-5">

                        <input type="text" name="payment" id="payment" style="font-size: 1.5em" value="{{$target_record_data['payment_comma'] ?? 0}}"
                            class="form-control form-control-sm input-payment text-right {{ $errors->has('payment') ? ' is-invalid' : '' }}"
                            disabled>

                    </div>
                </div>
                <div class="d-md-inline-flex col-sm-12 mt-2">
                    {{-- 支払後残高 --}}
                    <label class="col-sm-5 col-form-label col-form-label-sm text-right"
                           style="font-size: 1.5em">
                        <b>支払後残高</b>
                    </label>
                    {{-- 支払後残高 --}}
                    <div class="col-sm-5">

                        <input type="text" name="balance-after-payment" id="balance-after-payment" style="font-size: 1.5em" value="0"
                            class="form-control form-control-sm input-balance-after-payment text-right {{ $errors->has('balance-after-payment') ? ' is-invalid' : '' }}"
                            disabled>

                        @error('balance-after-payment')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-md-inline-flex col-sm-12 mb-2">
                    @error('payment')
                    <div class="invalid-feedback" style="font-weight: bold; font-size: 1.3em;">
                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="buttons-area text-center mt-2">
        <a id="return" class="btn btn-primary back_active"
           href="{{ session($session_common_key, route('trading.payments.index')) }}">
            一覧画面へ戻る
        </a>

        @if (config('consts.default.common.use_register_clear_button'))
            <a id="btn_clear" style="display:none;" onclick="clearInput();modalClose('#confirm-clear');"></a>
            <button id="clear" type="button" class="btn btn-secondary" data-toggle="modal" data-target="#confirm-clear">
                <i class="fas fa-times"></i>クリア
            </button>
        @endif

        @if (!$is_closing)
            {{-- 登録ボタン、更新ボタン --}}

            <button type="submit" class="btn btn-primary" id="btn_submit" style="display:none;">
                {{ $next_btn_text }}
            </button>

            <button type="button" id="store" class="btn btn-primary"
                    data-toggle="modal" data-target="#confirm-store" disabled>
                <i class="far fa-edit"></i>
                <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                     style="display: none;"></div>
                {{$next_btn_text}}
            </button>
        @endif

        {{-- 複製ボタン、削除ボタンは、編集画面のみ表示 --}}
        @if ($is_edit_route)
            {{-- 複製ボタン --}}
            <button id="copy" type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirm-copy"
                    style="display: none;">複製
            </button>
            @if (!$is_closing)
                {{-- 削除ボタン --}}
                <button id="delete" type="button" class="btn btn-danger" data-toggle="modal"
                        data-target="#confirm-delete">
                    <i class="fas fa-times"></i>
                    <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                         style="display: none;"></div>
                    削除
                </button>
            @endif
        @endif
    </div>

    {{-- エラー情報 --}}
    <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">
    {{-- 締処理確認用API --}}
    <input type="hidden" name="api_is_purchase_closing_url" value="{{ route('api.purchase_closing.is_closing') }}"
           class="hidden-api-is-purchase-closing-url">
    {{-- 単価取得用API --}}
    <input type="hidden" name="api_suppliers_get_payment_balance_url" value="{{ route('api.suppliers.get_payment_balance') }}" class="hidden-api-suppliers-get-payment-balance-url">

    <input type="hidden" id="default_order_date" name="default_order_date" value="{{ $default_order_date }}">

    </form>

    @if ($is_edit_route)

        <form name="deleteForm" id="deleteForm"
            action="{{ route('trading.payments.destroy', $target_record_data['id']) }}"
            method="POST">
        @csrf
        @method('DELETE')
        </form>

        <form name="copyForm" id="copyForm" action="{{ route('trading.payments.copy_order') }}" method="POST">
        @csrf

        @method('POST')
        {{-- 伝票ID用 --}}
        <input type="hidden" id="select_order_id" name="select_order_id" value="{{$target_record_data['id']}}">
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
        @slot('onclick_btn_ok', "destory();;return false;")
    @endcomponent

    {{-- Confirm Copy Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-copy')
        @slot('confirm_message', config('consts.message.common.confirm.copy') )
        @slot('onclick_btn_ok', "copy();return false;")
    @endcomponent

    {{-- Confirm Clear Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-clear')
        @slot('confirm_message', config('consts.message.common.confirm.clear') )
        @slot('onclick_btn_ok', "document.getElementById('btn_clear').click();")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/trading/payments/create_edit.js') }}"></script>

@endsection
