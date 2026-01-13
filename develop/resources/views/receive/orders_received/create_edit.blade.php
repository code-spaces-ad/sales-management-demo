{{-- 受注伝票入力画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.receive.menu.create');
    $next_url = route('receive.orders_received.store');
    $next_url2 = route('receive.orders_received.store_show_pdf');
    $next_btn_text = '登録';
    $andsale_btn_text = '売上確定';
    $method = 'POST';
    $is_edit_route = false;
    $order_number = '新規';
    $order_title = '受注番号';
    if (Route::currentRouteName() === 'receive.orders_received.edit') {
        $headline = config('consts.title.receive.menu.edit');
        $next_url = route('receive.orders_received.update', $target_record_data['id']);
        $next_url2 = route('receive.orders_received.update_show_pdf', $target_record_data['id']);
        $next_btn_text = '更新';
        $method = 'PUT';
        $is_edit_route = true;
        $order_number = $target_record_data['order_number_zerofill'];
    }

    // デフォルト伝票日付
    $default_order_date = \Carbon\Carbon::today()->toDateString();

    // デフォルト税率
    $default_tax_rate = $input_items['tax_rates'][0];

    /** @see MasterCustomersConst */
    $maxlength_customer_code = MasterCustomersConst::CODE_MAX_LENGTH; // 得意先コード最大桁数
    $min_customer_code = MasterCustomersConst::CODE_MIN_VALUE; // 得意先コード最小値
    /** @see MasterEmployeesConst */
    $maxlength_employee_code = MasterEmployeesConst::CODE_MAX_LENGTH; // 担当者コード最大桁数
    /** @see MasterProductsConst */
    $maxlength_product_code = MasterProductsConst::CODE_MAX_LENGTH; // 商品コード最大桁数
   /** @see MasterProductsConst */
    $min_product_code = MasterProductsConst::CODE_MIN_VALUE; // 商品コード最小値
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    @if ($target_record_data['order_status'] === \App\Enums\SalesConfirm::CONFIRM)
        <div class="row col-md-12 pb-1 pt-1 mb-2 btn-success" style="font-size: 1.5em;">
            納品済み
        </div>
    @endif
    <form name="editForm" id="editForm" action="{{ $next_url }}" method="POST">
        @method($method)
        @csrf

        <div class="row">
            <div class="col-12 px-0 px-md-4 pb-md-2">
                <div class="card">
                    @if (Route::currentRouteName() === 'receive.orders_received.edit')
                        <div class="card-header form-group d-md-inline-flex m-0">
                            {{-- 受注番号 --}}
                            @include('common.create_edit.order_number', ['title' => $order_title])
                            {{-- 更新者/更新日時テーブル --}}
                            @include('common.create_edit.updated_table')
                        </div>
                    @endif
                    <div class="card-body">
                        {{-- 伝票日付 --}}
                        @include('common.create_edit.order_date')

                        {{-- 得意先 --}}
                        @include('common.create_edit.customer_select_list')

                        <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                            {{-- 支所 --}}
                            @include('common.create_edit.branch_select_list')

                            {{-- 納品先 --}}
                            <div class="form-group d-md-inline-flex col-md-6 my-1">
                                <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                    <b>納品先</b>
                                </label>
                                <div class="flex-md-column col-md-9 pl-0">
                                    <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                        <input type="text" name="recipient_name" id="recipient_name"
                                               value="{{ old('recipient_name', $target_record_data['recipient_name'] ?? null) }}"
                                               class="form-control form-control-sm input-recipient-name mr-md-1 h-75{{ $errors->has('recipient_name') ? ' is-invalid' : '' }}"
                                               disabled/>
                                    </div>
                                    @error('recipient_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                            {{-- カラムオフセット --}}
                            <div class="form-group d-md-inline-flex offset-md-6 my-1"></div>
                            {{-- 納品先かな --}}
                            <div class="form-group d-md-inline-flex col-md-6 my-1">
                                <label class="col-md-3 col-form-label pl-0 pb-md-3">
                                    <b>納品先（かな）</b>
                                </label>
                                <div class="flex-md-column col-md-9 pl-0">
                                    <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                                        <input type="text" name="recipient_name_kana" id="recipient_name_kana"
                                               value="{{ old('recipient_name_kana', $target_record_data['recipient_name_kana'] ?? null) }}"
                                               class="form-control form-control-sm input-recipient-name-kana mr-md-1 h-75{{ $errors->has('recipient_name_kana') ? ' is-invalid' : '' }}"
                                               disabled/>
                                    </div>
                                    @error('recipient_name_kana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 担当者 --}}
                        <div class="row col-md-11 d-none">
                            <div class="form-group d-md-inline-flex col-sm-12 pl-0">
                                <label class="col-sm-4 col-form-label mr-1">
                                    <b>担当者</b>
                                    <span class="badge badge-danger align-middle">必須</span>
                                </label>
                                <div class="col-md-10 col-sm-12 ml-3 ml-md-0">
                                    <div class="d-md-inline-flex w-100 row">
                                        {{-- 担当者検索モーダル表示ボタン --}}
                                        <input type="number"
                                               class="form-control form-control-sm input-employee-code w-25 mr-2 col-4 col-sm-3"
                                               id="employee_code" oninput="inputCode(this);"
                                               onchange="changeEmployeeCode(this);"
                                               maxlength="{{ $maxlength_employee_code }}">
                                        {{-- 担当者 --}}
                                        <select name="employee_id" onchange="changeEmployee();"
                                                class="custom-select custom-select-sm col-sm-8.5 input-employee-select mr-md-5 select2_search @if ($errors->has('employee_id')) is-invalid @endif">
                                            <option value="">-----</option>
                                            @foreach (($input_items['employees'] ?? []) as $item)
                                                <option
                                                    @if ($item['id'] == old('employee_id', $target_record_data['employee_id'] ?? null))
                                                        selected
                                                    @endif
                                                    value="{{ $item['id'] }}"
                                                    data-code="{{ $item['code'] }}"
                                                    data-name="{{ $item['name'] }}">
                                                    {{ $item['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- メモ --}}
                        @include('common.create_edit.memo')
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                {{-- 商品テーブル --}}
                <div class="table table-fixed">
                    @error('detail')
                    <div class="invalid-feedback" style="font-weight: bold; font-size: 1.3em;">
                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                    </div>
                    @enderror
                    <table class="table table-bordered table-responsive-org mb-1 table-list" id="order_products_table">
                        <thead class="thead-light text-center">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">印刷</th>
                            <th scope="col"></th>
                            <th scope="col" class="col-md-5">商品</th>
                            <th scope="col" class="col-md-1">数量</th>
                            <th scope="col" class="col-md-1">納品日</th>
                            <th scope="col" class="col-md-1">倉庫</th>
                            <th scope="col" class="col-md-2">備考</th>
                            <th scope="col">売上</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $order_details = collect();
                            if (!empty($target_record_data['ordersReceivedDetail'])) {
                                $order_details = collect(old('detail', $target_record_data->ordersReceivedDetail()->get() ?? []));
                            }
                            $target_detail_count = config('consts.default.orders_received.product_row_count');   // 商品行の枠数
                            $actual_detail_count = count($order_details);

                            if ($actual_detail_count < $target_detail_count) {
                                $shortage_count = $target_detail_count - $actual_detail_count;

                                for ($i = 0; $i < $shortage_count; $i++) {
                                    $order_details->push([]);
                                }
                            }
                            if ($target_detail_count < 1) {
                                $order_details->push([],[],[],[],[]);
                            }
                        @endphp

                        {{-- 明細行 --}}
                        @foreach ($order_details as $key => $detail)
                            <tr>
                                {{-- No. --}}
                                <td class="text-center align-middle row-number sphone-no-display">
                                    {{ $key + 1 }}
                                </td>
                                <th scope="row" class="text-center align-middle pc-no-display">
                                    {{ $key + 1 }}
                                </th>
                                {{-- 納品書印刷 --}}
                                <td class="text-center align-middle" data-title="印刷">
                                    <div class="icheck-primary text-center w-100 m-0">
                                        <input type="checkbox" name="detail[{{ $key }}][delivery_print]"
                                               id="delivery-print-{{ $key }}" value="0"
                                               {{ old("detail.{$key}.delivery_print", $detail['delivery_print'] ?? false) ? 'checked' : '' }}
                                               class="form-check-input input-delivery-print clear-check clear-value change-disabled{{ $errors->has("detail.{$key}.delivery_print") ? ' is-invalid' : '' }}"/>

                                        <label class="label-delivery-print" for="delivery-print-{{ $key }}"></label>
                                    </div>
                                </td>
                                {{-- 行の追加・削除 --}}
                                <td data-title="行操作">
                                    @include('components.create_edit.row_add_delete')
                                </td>
                                {{-- 商品 --}}
                                <td data-title="商品">
                                    @component('components.create_edit.product_select_list')
                                        @slot('key', $key)
                                        @slot('input_items', $input_items)
                                        @slot('detail', $detail)
                                    @endcomponent
                                </td>
                                {{-- 数量 --}}
                                <td class="text-center align-middle" data-title="数量">
                                    <div class="d-block w-100 d-md-flex">
                                        <div class="col-md-12 mb-0 pl-0 pr-0">
                                            <input type="text" name="detail[{{ $key }}][quantity]"
                                                   value="{{ old("detail.{$key}.quantity", $detail['quantity'] ?? '') }}"
                                                   class="form-control form-control-sm input-quantity clear-value text-right mr-1{{ $errors->has("detail.{$key}.quantity") ? ' is-invalid' : '' }}"
                                                   inputmode="numeric" onchange="changeQuantityType(this)"/>
                                        </div>
                                        <div class="col-12 mt-1 mb-md-0 p-0 text-right pc-no-display">
                                            <input class="input-quantity-minus" type="checkbox"
                                                   id="input-quantity-minus-{{$key}}"
                                                   data-toggle="toggle" data-on="マイナス" data-off="プラス"
                                                   data-onstyle="primary" data-offstyle="danger" data-width="100"
                                                   onchange="changeQuantityType(this)">
                                        </div>
                                    </div>
                                    @error("detail.{$key}.quantity")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 納品日 --}}
                                <td class="text-center align-middle" data-title="納品日">
                                    <input type="date" name="detail[{{ $key }}][delivery_date]"
                                           value="{{ old("detail.{$key}.delivery_date", isset($detail['delivery_date']) ? \Carbon\Carbon::parse($detail['delivery_date'])->format('Y-m-d') : '') }}"
                                           class="form-control form-control-sm input-delivery-date clear-value mr-1{{ $errors->has("detail.{$key}.delivery_date") ? ' is-invalid' : (isset($detail['delivery_date']) ? ' disabled' : '') }}"
                                           onchange="makeWarehouseNameRequired(this);" {{ isset($detail['delivery_date']) ? 'disabled' : '' }} />

                                    @error("detail.{$key}.delivery_date")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 倉庫名 --}}
                                <td class="text-center align-middle" data-title="倉庫名">
                                    <select name="detail[{{ $key }}][warehouse_id]"
                                            class="custom-select custom-select-sm input-warehouse-name-select clear-select change-disabled text-center
                                            @if ($errors->has("detail.{$key}.warehouse_id")) is-invalid @endif"
                                            onchange="changeWarehouseName(this);"
                                            @if (!isset($detail['delivery_date'])) disabled @endif>
                                        <option value="">-----</option>
                                        @foreach (($input_items['warehouses'] ?? []) as $item)
                                            <option
                                                @if ($item['id'] == old("detail.{$key}.warehouse_id", $detail['warehouse_id'] ?? null))
                                                    selected
                                                @endif
                                                value="{{ $item['id'] }}"
                                                data-code="{{ $item['code'] }}">
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("detail.{$key}.warehouse_id")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 備考 --}}
                                <td class="text-center align-middle" data-title="備考">
                                    <input type="text" name="detail[{{$key}}][note]"
                                           value="{{ old('detail.{$key}.note',  $detail['note'] ?? '') }}"
                                           class="form-control form-control-sm input-note clear-value mr-1{{ $errors->has('detail.{$key}.note') ? ' is-invalid' : '' }}"/>

                                    @error("detail.{$key}.note")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                {{-- 売上確定 --}}
                                <td class="text-center align-middle" data-title="売上">
                                    <div class="icheck-primary text-center w-100 m-0">
                                        <input type="checkbox" name="detail[{{ $key }}][sales_confirm]" value="1"
                                               class="form-check-input input-sales-confirm clear-check change-disabled"
                                               id="sales-confirm-{{ $key }}" disabled checked>

                                        <label class="label-sales-confirm" for="sales-confirm-{{ $key }}"></label>
                                    </div>
                                </td>
                                {{-- クリアボタン --}}
                                <td class="text-center align-middle" data-title="クリア">
                                    <div class="text-center w-100">
                                        <button type="button" class="btn btn-secondary btn-xs button-product-clear m-0"
                                                onclick="clearProduct(this);"></button>
                                    </div>
                                </td>
                                <input type="hidden" name="detail[{{ $key }}][sort]" value="{{ $key }}" id="sort"
                                       class="input-sort">

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <input type="hidden" name="copy_number" value="0" id="copy_number">

        <span class="hr"></span>

        <div class="buttons-area text-center mt-2">
            <div class="row col-md-12 p-0 m-0">
                <div class="row col-md-5 flex-row-reverse mx-auto d-block p-0 text-center text-md-right">
                    <button id="show_pdf" type="button" class="btn btn-danger"
                            onclick="document.getElementById('showPdfForm').submit();return false;" disabled>
                        <i class="fas fa-file-pdf"></i>
                        納品書(PDF)
                    </button>

                    <button id="confirm_show_pdf" type="button" class="btn btn-success"
                            data-toggle="modal" data-target="#confirm-store-showpdf" disabled>
                        <i class="fas fa-file-pdf"></i>
                        {{ $next_btn_text }}して納品書(PDF)を出力
                    </button>
                </div>
                {{-- 縦線 --}}
                <div class="d-flex align-items-center sphone-no-display">｜</div>
                <div class="row flex-row-reverse mx-auto d-block mt-2 mt-md-0 p-0 text-center text-md-right">
                    <a id="return" class="btn btn-primary back_active"
                       href="{{ session($session_common_key, route('receive.orders_received.index')) }}">
                        一覧画面へ戻る
                    </a>
                </div>
                <div class="row col-md-5 flex-row-reverse mx-auto d-block mt-2 mt-md-0 p-0 text-center text-md-left">
                    @if (config('consts.default.common.use_register_clear_button'))
                        <a class="btn btn-secondary" onclick="clearInput();">クリア</a>
                    @endif

                    {{-- 登録ボタン、更新ボタン --}}
                    <input type="submit" value="次へ" class="btn btn-primary" id="btn_submit" style="display:none;">

                    <button type="button" id="store" class="btn btn-primary mr-2"
                            data-toggle="modal" data-target="#confirm-store">
                        <i class="far fa-edit"></i>
                        <div class="spinner-border spinner-border-sm text-light align-middle" role="status"
                             style="display: none;"></div>
                        {{ $next_btn_text }}
                    </button>

                    {{-- 複製ボタン、削除ボタンは、編集画面のみ表示 --}}
                    @if ($is_edit_route)
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
                </div>
            </div>
        </div>

        {{-- エラー情報 --}}
        <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">

        {{-- モーダル変更対象行 保存用 --}}
        <input type="hidden" id="modal_target_row" name="modal_target_row" value="">

        {{-- 画面の判別用--}}
        <input type="hidden" id="screen_name" name="screen_name" value="{{ \App\Enums\ScreenName::ORDERS_RECEIVED }}">

        <input type="hidden" name="autocomplete_list_recipient_name"
               value="{{ route('autocomplete.list_recipient_name') }}" class="hidden-autocomplete-list-recipient-name">
        <input type="hidden" name="autocomplete_list_recipient_name_kana"
               value="{{ route('autocomplete.list_recipient_name_kana') }}"
               class="hidden-autocomplete-list-recipient-name-kana">

        <input type="hidden" id="default_order_date" name="default_order_date" value="{{ $default_order_date }}">
        <input type="hidden" id="next_url2" name="next_url2" value="{{ $next_url2 }}">

    </form>

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm"
              action="{{ route('receive.orders_received.destroy', $target_record_data['id']) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>

        <form name="copyForm" id="copyForm" action="{{ route('receive.orders_received.copy_order') }}" method="POST">
            @method('POST')
            {{-- 伝票ID用 --}}
            <input type="hidden" id="select_orders_received_id" name="select_orders_received_id"
                   value="{{ $target_record_data['id'] }}">
        </form>

        <form name="showPdfForm" id="showPdfForm" action="{{ route('receive.orders_received.show_pdf') }}"
              target="_blank" rel="noopener noreferrer">
            @method('GET')
            {{-- 伝票ID用 --}}
            <input type="hidden" name="id" value="{{ $target_record_data['id'] }}">
            @foreach ($order_details as $key => $detail)
                <input type="hidden" name="detail[{{ $key }}][delivery_print]"
                       value="{{ old('detail.' . $key . '.delivery_print', $detail['delivery_print'] ?? 0) }}">
            @endforeach
        </form>
    @endif

    {{-- Confirm Store Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-store')
        @if ($is_edit_route)
            @slot('confirm_message', config('consts.message.common.confirm.update'))
        @else
            @slot('confirm_message', config('consts.message.common.confirm.store'))
        @endif
        @slot('onclick_btn_ok', "store();return false;")
    @endcomponent

    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-store-showpdf')
        @if($is_edit_route)
            @slot('confirm_message', config('consts.message.common.confirm.update'))
        @else
            @slot('confirm_message', config('consts.message.common.confirm.store'))
        @endif
        @slot('onclick_btn_ok', "storeAndShowPdf();return false;")
    @endcomponent

    {{-- Confirm Delete Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-delete')
        @slot('confirm_message', config('consts.message.common.confirm.delete'))
        @slot('onclick_btn_ok', "destory();return false;")
    @endcomponent

    {{-- Confirm Copy Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-copy')
        @slot('confirm_message', config('consts.message.common.confirm.copy'))
        @slot('onclick_btn_ok', "copy('". $copy_order_store_route ."');return false;")
    @endcomponent

    {{-- Search Custmoer Modal --}}
    @component('components.search_customer_modal')
        @slot('modal_id', 'search-customer-delivery')
        @slot('customers', $input_items['customers'])
        @slot('onclick_select_customer', "selectCustomerDeliverySearchCustomerModal(this);")
    @endcomponent

    {{-- Search Product Modal --}}
    @component('components.search_product_modal')
        @slot('modal_id', 'search-product')
        @slot('products', $input_items['products'])
        @slot('onclick_select_product', "selectProductSearchProductModal(this);")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/receive/orders_received/create_edit.js') }}"></script>

@endsection
