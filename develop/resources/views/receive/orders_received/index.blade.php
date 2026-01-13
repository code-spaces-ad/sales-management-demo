{{-- 受注伝票一覧画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.receive.menu.index');
    $next_url = route('receive.orders_received.index');
    $excel_download_url = route('receive.orders_received.download_excel');
    $method = 'GET';
    /** @see MasterEmployeesConst */
    $maxlength_employee_code = MasterEmployeesConst::CODE_MAX_LENGTH;   // 担当者コード最大桁数
    /** @see MasterCustomersConst */
    $maxlength_customer_id_code = MasterCustomersConst::CODE_MAX_LENGTH;   // 得意先コード最大桁数

    // デフォルトMAX日付・月
    $default_max_date = config('consts.default.common.default_max_date');
    $default_max_month = config('consts.default.common.default_max_month');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">

        <form name="searchForm" action="{{ $next_url }}" method="{{ $method }}" class="col-12 px-0 px-md-4 pb-md-2">

            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-header">
                    検索項目
                </div>
                <div class="card-body">
                    {{-- 受注日 --}}
                    @include('common.index.order_date')

                    {{-- 得意先 --}}
                    @include('common.index.customer_select_list')

                    <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                        {{-- 支所 --}}
                        @include('common.index.branch_select_list')

                        {{-- 納品先 --}}
                        @include('common.index.recipient_select_list')
                    </div>

                    {{-- 担当者 --}}
                    @include('common.index.employee_select_list')

                    {{-- 納品状況 --}}
                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        <label class="col-2 col-form-label my-1 pl-0">
                            <b>納品</b>
                        </label>
                        <div class="col-10 d-flex align-items-center pr-md-0">
                            <div class="icheck-primary icheck-inline">

                                <input type="checkbox" name="undelivered_only" id="undelivered_only_checkbox" value="1" {{ ($search_condition_input_data['undelivered_only'] ?? false) ? 'checked' : '' }}
                                    class="form-check-input input-undelivered-only clear-check{{ $errors->has('undelivered_only') ? ' is-invalid' : '' }}" />

                                <label class="form-check-label m-0" for="undelivered_only_checkbox">
                                    未納品あり
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    {{-- 検索・クリアボタン --}}
                    @include('common.index.search_clear_button')
                </div>
            </div>
        </form>

        <div class="col-md-12 d-flex justify-content-between mb-2">
            <div class="download-area">
                <div class="d-inline-flex">
                    <div class="mr-2">

                        <form name="downloadForm" action="{{ $excel_download_url }}" id="download_form" method="POST">
                        @method($method)
                        @csrf

                            <input type="hidden" name="order_date[start]" value="{{ old('order_date.start',$search_condition_input_data['order_date']['start'] ?? null) }}">
                            <input type="hidden" name="order_date[end]" value="{{ old('order_date.end',$search_condition_input_data['order_date']['end'] ?? null) }}">
                            <input type="hidden" name="customer_id" value="{{ $search_condition_input_data['customer_id'] ?? null }}">
                            <input type="hidden" name="employee_id" value="{{ $search_condition_input_data['employee_id'] ?? null }}">
                            <input type="hidden" name="branch_id" value="{{ $search_condition_input_data['branch_id'] ?? null }}">
                            <input type="hidden" name="recipient_id" value="{{ $search_condition_input_data['recipient_id'] ?? null }}">
                            <input type="hidden" name="undelivered_only" value="{{ $search_condition_input_data['undelivered_only'] ?? null }}">

                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- 明細行 --}}
        <div class="col-md-12">
            <div class="form-group d-md-inline-flex col-12 px-0 m-0">
                <div class="form-group d-md-inline-flex col-md-6 p-0 m-0">
                    {{ $search_result['orders_received']->appends($search_condition_input_data)->links() }}
                </div>
                <div class="col-md-6 m-0 p-0 text-right">
                    {{-- 新規登録ボタン --}}
                    @component('components.index.create_button')
                        @slot('route', route('receive.orders_received.create'))
                    @endcomponent
                </div>
            </div>
            <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                <table class="table table-bordered table-responsive-org table-list">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th scope="col" style="width: 2%;">
                            <a class="centralOpen">+</a>
                            <a class="centralClose float-none" style="display: none;">-</a>
                        </th>
                        <th scope="col">納品</th>
                        <th scope="col">売上</th>
                        <th scope="col" class="col-md-1">受注番号</th>
                        <th scope="col" class="col-md-1">受注日</th>
                        <th scope="col" class="col-md-4">得意先名</th>
                        <th scope="col" class="col-md-2">支所名</th>
                        <th scope="col" class="col-md-2">納品先名</th>
                        <th scope="col" class="col-md-1">担当</th>
                        <th scope="col" class="col-md-1">更新者</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($search_result['orders_received'] ?? [] as $orders_received)
                        <tr>
                            <th scope="row" class="text-center align-middle pc-no-display align-middle"
                                data-title="詳細">
                                <a class="open text-light">詳細:+</a>
                                <a class="close text-light float-none" style="display: none;">詳細:-</a>
                            </th>
                            <td class="text-center align-middle sphone-no-display align-middle" data-title="詳細">
                                <a class="open">+</a>
                                <a class="close float-none" style="display: none;">-</a>
                            </td>
                            {{-- 納品状況 --}}
                            <td class="text-center align-middle" data-title="納品">
                                @if ($orders_received->order_status === \App\Enums\SalesConfirm::CONFIRM)
                                    <div class="btn btn-success">完</div>
                                @else
                                    <div class="btn btn-secondary">未</div>
                                @endif
                            </td>
                            {{-- 売上状況 --}}
                            <td class="text-center align-middle" data-title="売上">
                                @if ($orders_received->getSalesConfirmFlg())
                                    <div class="btn btn-success">完</div>
                                @else
                                    <div class="btn btn-secondary">未</div>
                                @endif
                            </td>

                            {{-- 受注番号 --}}
                            <td class="text-center align-middle" data-title="受注番号">
                                <a href="{{ route('receive.orders_received.edit', $orders_received->id) }}">
                                    {{ $orders_received->order_number_zerofill }}
                                </a>
                            </td>
                            {{-- 受注日 --}}
                            <td class="text-center align-middle" data-title="受注日">
                                {{ $orders_received->order_date_slash }}
                            </td>
                            {{-- 得意先名 --}}
                            <td class="text-left align-middle" data-title="得意先名">
                                {{ $orders_received->mCustomer->name }}
                            </td>
                            {{-- 支所名 --}}
                            <td class="text-left align-middle" data-title="支所名">
                                {{ $orders_received->mBranch->name ?? null }}
                            </td>
                            {{-- 納品先名 --}}
                            <td class="text-left align-middle" data-title="納品先名">
                                {{ $orders_received->mRecipient->name ?? null }}
                            </td>
                            {{-- 担当 --}}
                            <td class="text-left align-middle" data-title="担当">
                                {{ $orders_received->employee_name }}
                            </td>
                            {{-- 更新者 --}}
                            <td class="text-left align-middle" data-title="更新者">
                                {{ $orders_received->updated_name }}
                            </td>
                        </tr>
                        <tr class="detail" style="display: none;">
                            <td colspan="10">
                                <table class="table table-fixed mb-1 table-th-white"
                                       id="order_products_table"
                                       style="max-height: none !important;">
                                    <thead class="thead-light text-center border-md-silver">
                                    <tr class="d-none d-md-table-row">
                                        <th style="width: 3%;">No.</th>
                                        <th style="width: 36%;">商品</th>
                                        <th style="width: 7%;">数量</th>
                                        <th style="width: 13%;">納品日</th>
                                        <th style="width: 13%;">倉庫名</th>
                                        <th style="width: 3%;">売上</th>
                                        <th style="width: 13%;">備考</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($orders_received->ordersReceivedDetail as $key => $detail)
                                        <tr>
                                            <th class="text-center align-middle border-secondary border-md-none">
                                                {{ ++$key }}
                                            </th>
                                            <td class="text-left align-middle border-secondary border-top-0 border-md-none"
                                                data-title="商品">
                                                {{ $detail->product_name }}
                                            </td>
                                            <td class="text-right align-middle border-secondary border-top-0 border-md-none"
                                                data-title="数量">
                                                {{ number_format($detail->quantity) }}
                                            </td>
                                            {{-- 納品日 --}}
                                            <td class="text-center align-middle border-secondary border-top-0 border-md-none"
                                                data-title="納品日">
                                                {{ $detail->delivery_date_slash }}
                                            </td>
                                            {{-- 倉庫名 --}}
                                            <td class="text-center align-middle border-secondary border-top-0 border-md-none"
                                                data-title="倉庫名">
                                                {{ $detail->warehouse_name }}
                                            </td>
                                            {{-- 売上状況 --}}
                                            <td class="text-center align-middle border-secondary border-top-0 border-md-none"
                                                data-title="売上">
                                                @if ($detail->sales_confirm)
                                                    <div class="btn btn-success">完</div>
                                                @else
                                                    <div class="btn btn-secondary">未</div>
                                                @endif
                                            </td>
                                            <td class="text-left align-middle border-secondary border-top-0 border-md-none"
                                                data-title="備考">
                                                {{ $detail->note }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $search_result['orders_received']->appends($search_condition_input_data)->links() }}
        </div>
    </div>

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/receive/orders_received/index.js') }}"></script>
@endsection
