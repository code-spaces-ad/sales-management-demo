{{-- 仕入伝票入力画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.trading.menu.create');
    $next_url = route('trading.purchase_orders.store');
    $api_url = route('api.purchase_closing.is_closing');
    $next_btn_text = '登録';
    $method = 'POST';
    $is_edit_route = false;
    $is_closing = false;
    $is_pos = false;
    $order_number = '新規';
    $order_title = '伝票番号';
    if (Route::currentRouteName() === 'trading.purchase_orders.edit') {
        $headline = config('consts.title.trading.menu.edit');
        $next_url = route('trading.purchase_orders.update', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
        $is_closing = !is_null($target_record_data['closing_at']);
        $is_pos = $target_record_data['link_pos'];
        $order_number = $target_record_data['order_number_zerofill'];
    }
    // コピー用route
    $copy_order_store_route = route('trading.purchase_orders.store');

    // デフォルト伝票日付
    $default_transaction_id = config('consts.default.common.transaction_type_id', 2);    // デフォルト取引種別ID
    $default_order_date = \Carbon\Carbon::today()->toDateString();  // デフォルト伝票日付
    $default_tax_rate = config('consts.default.common.consumption_tax_rate', 0);    // デフォルト税率
    $default_sub_total_rounding_method = config('consts.default.common.sub_total_rounding_method' ?? 3);

    /** @see MasterVendorsConst */
    $maxlength_supplier_code = MasterSuppliersConst::CODE_MAX_LENGTH;   // 業者（仕入先）コード最大桁数
    /** @see MasterProductsConst */
    $maxlength_product_code = MasterProductsConst::CODE_MAX_LENGTH;   // 商品コード最大桁数
    /** @see MasterProductsConst */
    $min_product_code = MasterProductsConst::CODE_MIN_VALUE;   // 商品コード最小値
    /** 税計算使用有無 */
    $is_tax_calc_use_flag = config('consts.tax_calc.use_flag');
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
                    現在入力中の仕入先・締日は締データが存在するため登録できません。
                </div>
            @endif
            @if ($is_pos)
                <div class="row col-md-12 pb-1 pt-1 mb-2 btn-warning" style="font-size: 1.5em;">POS連携データ</div>
            @endif
            <div class="col-12 px-0 px-md-4 pb-md-2">
                <div class="card">
                    @if(Route::currentRouteName() === 'trading.purchase_orders.edit')
                        <div class="card-header form-group d-md-inline-flex m-0">
                            {{-- 伝票番号 --}}
                            @include('common.create_edit.order_number', ['title' => $order_title])
                            {{-- 更新者/更新日時テーブル --}}
                            @include('common.create_edit.updated_table')
                        </div>
                    @endif

                    <div class="card-body">
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
                                                onchange="checkChargeClosed();">
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

                            {{-- 仕入分類 --}}
                            @php
                                $disabled_purchase_classification = true;
                                $required_purchase_classification = true;
                            @endphp
                            @include('common.create_edit.purchase_classification_select_list')
                        </div>

                        <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                            {{-- 仕入日 --}}
                            @include('common.create_edit.order_date')

                            {{-- 締日 --}}
                            <div class="form-group d-md-inline-flex col-md-6 my-1">
                                <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                    <b>締日</b>
                                    <span class="badge badge-danger">必須</span>
                                </label>
                                <div class="flex-md-column col-md-4 pl-0">
                                    <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                        <input type="date" name="closing_date"
                                               value="{{ old('closing_date', $target_record_data['closing_date'] ?? $default_order_date) }}"
                                               class="form-control input-closing-date clear-value {{ $errors->has('closing_date') ? 'is-invalid' : '' }}"
                                               id="closing_date"
                                               onchange="checkPurchaseClosed();"
                                               onblur="blurOrderDate(this);">
                                    </div>
                                    @error('closing_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                            {{-- 仕入先 --}}
                            @include('common.create_edit.supplier_select_list')
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
                            {{-- メモ --}}
                            @include('common.create_edit.memo')

                            {{-- 値引額 --}}
                            <div class="form-group d-md-inline-flex col-md-6 my-1">
                                <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                    <b>伝票値引額</b>
                                </label>
                                <div class="flex-md-column col-md-9 pr-md-0 pl-0">
                                    <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                        {{-- 値引額 --}}
                                        <input type="text" onchange="setTotalAmounts()"
                                               class="form-control h-75 input-discount input-order-discount text-right col-5 col-md-4 mr-md-1"
                                               name="discount"
                                               value="{{ old('discount', $target_record_data['discount'] ?? 0) }}"
                                               >
                                    </div>
                                    @error('discount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="tax_calc_type_id" value="{{ old('tax_calc_type_id', $target_record_data['tax_calc_type_id'] ?? '') }}" class="hidden-tax-calc-type-id">

            <div class="col-md-12">
                {{-- 商品テーブル --}}
                @error('detail')
                <div class="invalid-feedback" style="font-weight: bold; font-size: 1.3em;">
                    <i class="fas fa-exclamation-circle"></i>{{ $message }}
                </div>
                @enderror
                @error('detail.*')
                <div class="invalid-feedback" style="font-weight: bold; font-size: 1.3em;">
                    <i class="fas fa-exclamation-circle"></i>明細行の{{ $message }}
                </div>
                @enderror

                <div class="table-responsive table-fixed">
                    <table class="table table-bordered table-responsive-org mb-1 table-list" id="order_products_table">
                        <thead class="thead-light text-center">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col"></th>
                            <th scope="col" class="col-md-5">商品</th>
                            <th scope="col" class="col-md-1">数量</th>
                            <th scope="col" class="col-md-1 d-none">単位</th>
                            <th scope="col" class="col-md-1">仕入単価</th>
                            <th scope="col" class="col-md-1">税率</th>
                            <th scope="col" class="col-md-1">値引額</th>
                            <th scope="col" class="col-md-1">
                                金額
                                <span class="d-inline-block" tabindex="0" data-toggle="tooltip"
                                      title="金額の端数処理は「{{ RoundingMethodType::getDescription(config('consts.default.common.sub_total_rounding_method' ?? 3)) }}」です">
                                        <i class="fas fa-info-circle"></i>
                                    </span>
                            </th>
                            <th scope="col" class="col-md-2">備考</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody id="sortdata">
                        @php
                            $order_details = old('detail', $target_record_data['purchaseOrderDetail']->toArray() ?? []);
                            $target_detail_count = config('consts.default.purchase_order.product_row_count');   // 商品行の枠数
                            $actual_detail_count = count($order_details);

                            if ($actual_detail_count < $target_detail_count) {
                                $shortage_count = $target_detail_count - $actual_detail_count;

                                for ($i = 0; $i < $shortage_count; $i++) {
                                    array_push($order_details, []);
                                }
                            }
                        @endphp
                        @foreach ($order_details as $key => $detail)
                            <tr>
                                {{-- No. --}}
                                <td class="text-center align-middle row-number sphone-no-display">
                                    {{ $key + 1 }}
                                </td>
                                <th scope="row" class="text-center align-middle pc-no-display">
                                    {{ $key + 1 }}
                                </th>
                                {{-- 行の追加・削除 --}}
                                <td data-title="行操作">
                                    @include('components.create_edit.row_add_delete')
                                </td>
                                {{-- 商品 --}}
                                <td data-title="商品">
                                    @component('components.create_edit.product_select_list_supplier_price_history')
                                        @slot('key', $key)
                                        @slot('input_items', $input_items)
                                        @slot('detail', $detail)
                                    @endcomponent
                                </td>
                                {{-- 数量 --}}
                                <td class="text-center align-middle" data-title="数量">

                                    <input type="text" name="detail[{{$key}}][quantity]"
                                           value="{{ old('detail.'.$key.'.quantity', $detail['quantity'] ?? null) }}"
                                           onfocus="getSupplierUnitPriceHistory(this);"
                                           class="form-control form-control-sm input-quantity text-right mr-1 {{ $errors->has('detail.'.$key.'.quantity') ? 'is-invalid' : '' }}"
                                           inputmode="numeric" min="1">

                                    @error("detail.{$key}.quantity")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 単位 --}}
                                <td class="text-center align-middle d-none" data-title="単位">
                                    <select name="detail[{{ $key }}][unit_name]"
                                            class="custom-select custom-select-sm input-product-unit-name-select text-center
                                            @if($errors->has("detail.{$key}.unit_name")) is-invalid @endif"
                                            onchange="changeUnitName(this);"
                                            onfocus="getSupplierUnitPriceHistory(this);">
                                        <option value="">-----</option>
                                        @foreach (($input_items['units'] ?? []) as $item)
                                            <option
                                                @if ($item['name'] == old("detail.{$key}.unit_name", $detail['unit_name'] ?? null))
                                                    selected
                                                @endif
                                                value="{{ $item['name'] }}">
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("detail.{$key}.unit_name")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 仕入単価 --}}
                                <td class="text-center align-middle" data-title="仕入単価">

                                    <input type="text" name="detail[{{ $key }}][unit_price]"
                                           value="{{ old("detail.{$key}.unit_price", $detail['unit_price'] ?? null) }}"
                                           onfocus="getSupplierUnitPriceHistory(this);"
                                           class="form-control form-control-sm text-right mr-1 input-unit-price{{ $errors->has("detail.{$key}.unit_price") ? ' is-invalid' : '' }}"/>

                                    @error("detail.{$key}.unit_price")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 税率 --}}
                                <td class="text-center align-middle" data-title="税率">
                                    <div class="d-inline-flex w-100">
                                        <label class="col-form-label col-form-label-sm label-tax-type-name text-nowrap mr-1"
                                               onfocus="getCustomerUnitPriceHistory(this);">
                                            [税抜]
                                        </label>
                                        {{-- 税率 --}}
                                        <input type="text" name="detail[{{ $key }}][consumption_tax_rate]"
                                               value="{{ old("detail.{$key}.consumption_tax_rate", $detail['consumption_tax_rate'] ?? $default_tax_list['normal_tax_rate']) }}"
                                               onfocus="getCustomerUnitPriceHistory(this);"
                                               class="form-control form-control-sm input-consumption-tax-rate-select clear-value mr-1 {{ ($errors->has("detail.{$key}.consumption_tax_rate") ? ' is-invalid' : '') }}"
                                               placeholder="税率">
                                        <label class="col-form-label col-form-label-sm label-tax-unit-name text-nowrap mr-1"
                                               onfocus="getCustomerUnitPriceHistory(this);">
                                            ％
                                        </label>
                                    </div>
                                    @error("detail.{$key}.consumption_tax_rate")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 値引額 --}}
                                <td class="text-center align-middle" data-title="値引額">

                                    <input type="text" name="detail[{{$key}}][discount]"
                                           value="{{ old('detail.'.$key.'.discount', $detail['discount'] ?? null) }}"
                                           onfocus="getSupplierUnitPriceHistory(this);"
                                           class="form-control form-control-sm input-discount text-right mr-1 {{ $errors->has('detail.'.$key.'.discount') ? 'is-invalid' : '' }}"
                                           inputmode="numeric" min="0">

                                    @error("detail.{$key}.discount")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 金額 --}}
                                <td class="text-center align-middle" data-title="金額">
                                    <div class="w-100 mr-1">
                                        <div class="d-inline-flex w-100">
                                            <input type="text"
                                                   onfocus="getSupplierUnitPriceHistory(this);"
                                                   class="form-control form-control-sm input-product-sub-total text-right"
                                                   disabled>
                                        </div>
                                    </div>

                                    <input type="hidden" name="detail[{{ $key }}][consumption_tax_rate]"
                                           value="{{ old("detail.{$key}.consumption_tax_rate", $detail['consumption_tax_rate'] ?? null) }}"
                                           class="hidden-consumption-tax-rate"/>


                                    {{-- 単価小数桁数 --}}
                                    <input type="hidden" name="detail[{{ $key }}][unit_price_decimal_digit]"
                                           value="{{ old("detail.{$key}.unit_price_decimal_digit", 0) }}"
                                           class="hidden-unit-price-decimal-digit"/>


                                    {{-- 数量小数桁数 --}}
                                    <input type="hidden" name="detail[{{ $key }}][quantity_decimal_digit]"
                                           value="{{ old("detail.{$key}.quantity_decimal_digit", 0) }}"
                                           class="hidden-quantity-decimal-digit"/>


                                    {{-- 税区分 --}}
                                    <input type="hidden" name="detail[{{ $key }}][tax_type_id]"
                                           value="{{ old("detail.{$key}.tax_type_id", $detail['tax_type_id'] ?? 0) }}"
                                           class="hidden-tax-type-id"/>


                                    {{-- 軽減税率対象フラグ --}}
                                    <input type="hidden" name="detail[{{ $key }}][reduced_tax_flag]"
                                           value="{{ old("detail.{$key}.reduced_tax_flag", $detail['reduced_tax_flag'] ?? 0) }}"
                                           class="hidden-reduced-tax-flag"/>


                                    {{-- 税額端数処理 --}}
                                    <input type="hidden" name="detail[{{ $key }}][tax_rounding_method_id]"
                                           value="{{ old("detail.{$key}.tax_rounding_method_id", $detail['rounding_method_id'] ?? null) }}"
                                           class="hidden-tax-rounding-method-id"/>

                                    {{-- 税額 --}}
                                    <input type="hidden" name="detail[{{ $key }}][tax]"
                                           value="{{ old("detail.{$key}.tax", $detail['tax'] ?? 0) }}"
                                           class="hidden-tax"/>


                                    {{-- 金額端数処理 --}}
                                    <input type="hidden" name="detail[{{ $key }}][amount_rounding_method_id]"
                                           value="{{ old("detail.{$key}.amount_rounding_method_id", $detail['amount_rounding_method_id'] ?? null) }}"
                                           class="hidden-amount-rounding-method-id"/>

                                </td>

                                {{-- 備考 --}}
                                <td class="text-center align-middle" data-title="備考">
                                    <input type="text" name="detail[{{ $key }}][note]"
                                           value="{{ old("detail.{$key}.note", $detail['note'] ?? null) }}"
                                           onfocus="getSupplierUnitPriceHistory(this);"
                                           class="form-control form-control-sm input-detail-note mr-1{{ $errors->has("detail.{$key}.note") ? ' is-invalid' : '' }}"/>

                                    @error("detail.{$key}.note")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- クリアボタン --}}
                                <td class="text-center align-middle" data-title="クリア">
                                    <div class="text-center w-100">
                                        <button type="button"
                                                class="btn btn-secondary btn-xs button-product-clear m-0"
                                                onclick="clearProduct(this);">
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- 金額欄 --}}
                <div class="money-area mt-3" style="display: none; font-size: 10%;">
                    <div class="col-md-4 mx-auto">
                        <table class="table table-bordered ml-4">
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th style="width: 30%;">金額計</th>
                                <th style="width: 30%;">消費税</th>
                                <th style="width: 30%;">合計</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                {{-- 金額計 --}}
                                <td class="input-order-sub-total text-right">0</td>
                                {{-- 消費税  --}}
                                <td class="input-order-tax-total text-right">0</td>
                                {{-- 合計 --}}
                                <td class="input-order-total text-right">0</td>
                            </tr>
                            </tbody>
                            @error('sales_total')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </table>
                    </div>
                </div>

                {{-- 金額欄 --}}
                <div class="money-area d-flex mt-md-1 mt-4">

                    {{-- 単価履歴 --}}
                    @include('components.create_edit.price_history')

                    {{-- 税額欄 --}}
                    <div class="col-md-4">
                        <table class="table table-sm table-bordered" style="border: none;">
                            <tbody>
                            <tr>
                                {{-- 通常税率 対象額 --}}
                                <td style="width: 10%; background: #afeeee; font-size: 90%;" rowspan="2"
                                    class="text-center align-middle">通常税率 対象額
                                </td>
                                <td style="width: 20%; background-color: transparent; font-size: 90%;" rowspan="2"
                                    class="text-right align-middle text-consumption-total">&yen;0
                                </td>
                                <td class="text-center"
                                    style="width: 8%; background-color: transparent; font-size: 90%;">
                                    税抜
                                </td>
                                <td class="text-right text-purchase-total-normal-out"
                                    style="width: 10%; background-color: transparent; font-size: 90%;">&yen;0
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center"
                                    style="width: 8%; background-color: transparent; font-size: 90%;">
                                    税込
                                </td>
                                <td class="text-right text-purchase-total-normal-in"
                                    style="width: 10%; background-color: transparent; font-size: 90%;">(&yen;0)
                                </td>
                            </tr>
                            <tr>
                                {{-- 軽減税率 対象額 --}}
                                <td style="width: 10%; background: #afeeee; font-size: 90%;" rowspan="2"
                                    class="text-center align-middle">軽減税率 対象額
                                </td>
                                <td style="width: 20%; background-color: transparent; font-size: 90%;" rowspan="2"
                                    class="text-right align-middle text-reduced-total">&yen;0
                                </td>
                                <td class="text-center align-middle"
                                    style="width: 8%; background-color: transparent; font-size: 90%;">税抜
                                </td>
                                <td class="text-right align-middle text-purchase-total-reduced-out"
                                    style="width: 10%; background-color: transparent; font-size: 90%;">&yen;0
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center align-middle"
                                    style="width: 8%; background-color: transparent; font-size: 90%;">税込
                                </td>
                                <td class="text-right align-middle text-purchase-total-reduced-in"
                                    style="width: 10%; background-color: transparent; font-size: 90%;">(&yen;0)
                                </td>
                            </tr>
                            <tr>
                                {{-- 非課税対象額 --}}
                                <td style="width: 20%; background: #afeeee; font-size: 90%;"
                                    class="text-center align-middle">非課税対象額
                                </td>
                                <td style="width: 20%; background-color: transparent;"
                                    class="text-right align-middle text-notax-total">&yen;0
                                </td>
                                <td style="width: 10%; background-color: transparent;" colspan="2"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6 col-6">
                        <table class="table table-sm table-bordered" style="border: none;">
                            <tbody>
                            <tr>
                                {{-- 小計 --}}
                                <td style="width: 24%; background: lightgray; font-size: 90%;"
                                    class="text-center align-middle" colspan="2">小計
                                </td>
                                <td style="width: 21%; background-color: transparent;"
                                    class="text-right align-middle text-sub-total-out-discount">&yen;0
                                </td>
                                {{-- 空欄 --}}
                                <td class="border-0 invisible" colspan="4"></td>
                            </tr>
                            <tr>
                                {{-- 値引額 --}}
                                <td style="width: 24%; background: lightgray; font-size: 90%;"
                                    class="text-center align-middle" colspan="2">伝票値引額
                                </td>
                                <td style="width: 21%; background-color: transparent;"
                                    class="text-right align-middle text-order-discount">&yen;0
                                </td>
                                {{-- 空欄 --}}
                                <td class="border-0 invisible" colspan="4"></td>
                            </tr>
                            <tr>
                                {{-- 金額計(値引込) --}}
                                <td style="width: 24%; background: #afeeee; font-size: 90%; font-size: 1rem; font-weight: bold"¥
                                    class="text-center align-middle" colspan="2">金額計(伝票値引込)
                                </td>
                                <td style="width: 21%; background-color: transparent; font-size: 1.2rem; font-weight: bold"
                                    class="text-right align-middle text-sub-total">&yen;0
                                </td>
                                {{-- 空欄 --}}
                                <td class="border-0 invisible" colspan="4"></td>
                            </tr>
                            <tr>
                                <td style=" font-size: 0.7rem" class="text-center align-middle" rowspan="2">消費税</td>
                                {{-- 通常税率 --}}
                                <td style="width: 14%; background: #afeeee;"
                                    class="text-center align-middle">通常税率
                                </td>
                                <td style="width: 21%; background-color: transparent;"
                                    class="text-right align-middle text-consumption-tax">&yen;0
                                </td>
                                {{-- 空欄 --}}
                                <td class="border-0" colspan="4" rowspan="2"
                                    style="color: red; background-color: transparent !important; font-size: 0.8rem;">
                                    ※消費税：(税抜)商品が対象となります。<br>
                                    (税込商品の消費税は含まれておりません)<br>
                                    ※仕入先の税計算に準じて計算されます。
                                </td>
                            </tr>
                            <tr>
                                {{-- 軽減税率 --}}
                                <td style="width: 14%; background: #afeeee;"
                                    class="text-center align-middle">軽減税率
                                </td>
                                <td style="width: 21%; background-color: transparent;"
                                    class="text-right align-middle text-reduced-tax">&yen;0
                                </td>
                            </tr>
                            <tr>
                                {{-- 合計 --}}
                                <td style="width: 14%; background: #afeeee;
                                font-size: 1.2rem; font-weight: bold"
                                    class="text-center align-middle" colspan="2">
                                    合&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;計
                                </td>
                                <td style="width: 21%; background-color:transparent;
                                font-size: 1.2rem; font-weight: bold"
                                    class="text-right align-middle text-inctax-total">&yen;0
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        @error('sales_total')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div>

            <input type="hidden" name="copy_number" value="0" id="copy_number">

        </div>
        <div data-title="合計" style="display: none;">
            {{-- 今回仕入額 --}}
            今回仕入額

            <input type="text" name="purchase_total"
                   value="{{ old('purchase_total', $target_record_data['purchase_total'] ?? null) }}"
                   class="hidden-purchase-total">

        </div>
        <div data-title="各種税額" style="display: none;">
            {{-- 今回仕入額_通常税率_外税分 --}}
            今回仕入額_通常税率_外税分
            <input type="text" name="purchase_total_normal_out"
                   value="{{ old('purchase_total_normal_out', $target_record_data['purchase_total_normal_out'] ?? null) }}"
                   class="hidden-purchase-total-normal-out">
            <br>
            {{-- 今回仕入額_軽減税率_外税分 --}}
            今回仕入額_軽減税率_外税分
            <input type="text" name="purchase_total_reduced_out"
                   value="{{ old('purchase_total_reduced_out', $target_record_data['purchase_total_reduced_out'] ?? null) }}"
                   class="hidden-purchase-total-reduced-out">
            <br>
            {{-- 今回仕入額_通常税率_内税分 --}}
            今回仕入額_通常税率_内税分
            <input type="text" name="purchase_total_normal_in"
                   value="{{ old('purchase_total_normal_in', $target_record_data['purchase_total_normal_in'] ?? null) }}"
                   class="hidden-purchase-total-normal-in">
            <br>
            {{-- 今回仕入額_軽減税率_内税分 --}}
            今回仕入額_軽減税率_内税分
            <input type="text" name="purchase_total_reduced_in"
                   value="{{ old('purchase_total_reduced_in', $target_record_data['purchase_total_reduced_in'] ?? null) }}"
                   class="hidden-purchase-total-reduced-in">
            <br>
            {{-- 今回仕入額_非課税分 --}}
            今回仕入額_非課税分
            <input type="text" name="purchase_total_free"
                   value="{{ old('purchase_total_free', $target_record_data['purchase_total_free'] ?? null) }}"
                   class="hidden-purchase-total-free">
            <br>
            {{-- 消費税額_通常税率_外税分 --}}
            消費税額_通常税率_外税分
            <input type="text" name="purchase_tax_normal_out"
                   value="{{ old('sales_tax_normal_out', $target_record_data['purchase_tax_normal_out'] ?? null) }}"
                   class="hidden-purchase-tax-normal-out">
            <br>
            {{-- 消費税額_軽減税率_外税分 --}}
            消費税額_軽減税率_外税分
            <input type="text" name="purchase_tax_reduced_out"
                   value="{{ old('sales_tax_reduced_out', $target_record_data['purchase_tax_reduced_out'] ?? null) }}"
                   class="hidden-purchase-tax-reduced-out">
            <br>
            {{-- 消費税額_通常税率_内税分 --}}
            消費税額_通常税率_内税分
            <input type="text" name="purchase_tax_normal_in"
                   value="{{ old('purchase_tax_normal_in', $target_record_data['purchase_tax_normal_in'] ?? null) }}"
                   class="hidden-purchase-tax-normal-in">
            <br>
            {{-- 消費税額_軽減税率_内税分 --}}
            消費税額_軽減税率_内税分
            <input type="text" name="purchase_tax_reduced_in"
                   value="{{ old('purchase_tax_reduced_in', $target_record_data['purchase_tax_reduced_in'] ?? null) }}"
                   class="hidden-purchase-tax-reduced-in">

            @foreach ($errors->all() as $error)
                <li>{{$error}}</li>
            @endforeach
        </div>
        </div>

        <div class="buttons-area text-center mt-2">
            <a id="return" class="btn btn-primary back_active mr-2"
               href="{{ session($session_common_key, route('trading.purchase_orders.index')) }}">
                一覧画面へ戻る
            </a>

            @if (config('consts.default.common.use_register_clear_button'))
                <a class="btn btn-secondary" onclick="clearInput();">クリア</a>
            @endif

            @if (!$is_closing && !$is_pos)
                {{-- 登録ボタン、更新ボタン --}}
                <button type="submit" class="btn btn-primary" id="btn_submit" style="display:none;">
                    {{ $next_btn_text }}
                </button>

                <button type="button" id="store" class="btn btn-primary mr-2"
                        data-toggle="modal" data-target="#confirm-store" disabled>
                    <i class="far fa-edit"></i>
                    <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                         style="display: none;"></div>
                    {{ $next_btn_text }}
                </button>
            @endif

            {{-- 複製ボタン、削除ボタンは、編集画面のみ表示 --}}
            @if ($is_edit_route)
                @if (!$is_closing && !$is_pos)
                    {{-- 複製ボタン --}}
                    <button id="copy" type="button" class="btn btn-outline-primary mr-2" data-toggle="modal"
                            data-target="#confirm-copy">
                        <i class="fas fa-copy"></i>
                        <div class="spinner-border spinner-border-sm text-primary align-middle" role="status"
                             style="display: none;"></div>
                        複製
                    </button>
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
        {{-- 仕入単価取得用API --}}
        <input type="hidden" name="api_get_unit_price_url" value="{{ route('api.purchase_order.get_unit_price') }}"
               class="hidden-api-get-unit-price-url">
        {{-- モーダル変更対象行 保存用 --}}
        <input type="hidden" id="modal_target_row" name="modal_target_row" value="">
        {{-- 税計算使用有無 --}}
        <input type="hidden" id="tax_calc_use_flag" name="tax_calc_use_flag" value="{{ $is_tax_calc_use_flag }}">
        {{-- 画面の判別用--}}
        <input type="hidden" id="screen_name" name="screen_name" value="{{ \App\Enums\ScreenName::PURCHASE_ORDERS }}">
        {{-- デフォルト税率 --}}
        <input type="hidden" id="tax_rate" name="tax_rate" value="{{ $default_tax_list['normal_tax_rate'] }}">
        <input type="hidden" id="reduced_tax_rate" name="reduced_tax_rate" value="{{ $default_tax_list['reduced_tax_rate'] }}">
        {{-- デフォルト小計端数処理 --}}
        <input type="hidden" id="sub_total_rounding_method" name="sub_total_rounding_method"
               value="{{ $default_sub_total_rounding_method }}">
        {{-- サブディレクトリ --}}
        <input type="hidden" id="sub_dir" name="sub_dir" class="hidden-sub-dir"
               value="{{ env('MIX_ROOT_DIRECTORY_NAME') }}">

        <input type="hidden" id="default_order_date" name="default_order_date" value="{{ $default_order_date }}">
        <input type="hidden" id="link_pos" name="link_pos" value={{ $target_record_data['link_pos'] }}>
    </form>

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm"
              action="{{ route('trading.purchase_orders.destroy', $target_record_data['id']) }}" method="POST">
            @csrf
            @method('DELETE')
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

    {{-- Confirm Copy Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-copy')
        @slot('confirm_message', config('consts.message.common.confirm.copy') )
        @slot('onclick_btn_ok', "copy('". $copy_order_store_route ."');return false;")
    @endcomponent

    {{-- Confirm Clear Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-clear')
        @slot('confirm_message', config('consts.message.common.confirm.clear') )
        @slot('onclick_btn_ok', "document.getElementById('btn_clear').click();")
    @endcomponent

    {{-- Search Supllier Modal --}}
    @component('components.search_supplier_modal')
        @slot('modal_id', 'search-supplier')
        @slot('suppliers', $input_items['suppliers'])
        @slot('onclick_select_supplier', "selectSupplierSearchSupplierModal(this);")
    @endcomponent

    {{-- Search Product Modal --}}
    @component('components.search_product_modal')
        @slot('modal_id', 'search-product')
        @slot('products', $input_items['products'])
        @slot('onclick_select_product', "selectProductSearchProductModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/trading/purchase_orders/create_edit.js') }}"></script>

@endsection
