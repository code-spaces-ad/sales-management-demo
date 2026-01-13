{{-- 在庫入出庫入力画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.inventory.menu.stock_datas.create');
    $next_url = route('inventory.inventory_datas.store');
    $action = 'Inventory\InventoryController@store';
    $next_btn_text = '登録';
    $method = 'POST';
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

                    {{-- 商品名 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>{{ $product_data->code }}</b>
                        </label>
                        <div class="col-sm-6">
                            {{ $product_data->name }}
                        </div>
                    </div>

                    {{-- 倉庫名 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>倉庫名</b>
                        </label>
                        <div class="col-sm-6">
                            {{ $warehouse_data->name }}
                        </div>
                    </div>

                    {{-- 在庫数 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>在庫数</b>
                        </label>
                        <div class="col-sm-1 selected_stock pr-0">
                            {{ $target_record_data->inventory_stocks ?? 0 }}
                        </div>
                        <div class="selected_stock_error d-none">
                            <div class="invalid-feedback ml-1">※在庫がありません</div>
                        </div>
                    </div>

                    {{-- 入出庫日 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>入出庫日</b>
                            <span class="badge badge-danger align-middle">必須</span>
                        </label>
                        <div class="col-sm-2">

                            <input type="date" name="inout_date" value="{{ old('inout_date', \Carbon\Carbon::today()->toDateString()) }}"
                                class="form-control {{ $errors->has('inout_date') ? 'is-invalid' : '' }}">

                            @error('inout_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 入出庫 --}}
                    <div class="form-group row my-2">
                        <div class="col-6 col-md-2 icheck-primary icheck-inline">

                            <input type="radio" name="inout_data" id="entry" value="entry"
                                class="input-in-out-data align-middle" {{ old('inout_data') === 'entry' ? 'checked' : '' }}>

                            <label for="entry" class="mb-0">入庫</label>
                        </div>
                        <div class="col-6 col-md-2 icheck-primary icheck-inline">

                            <input type="radio" name="inout_data" id="issue" value="issue"
                                class="input-in-out-data align-middle" {{ old('inout_data') === 'issue' ? 'checked' : '' }}>

                            <label for="issue" class="mb-0">出庫</label>
                        </div>
                    </div>

                    {{-- 数量 --}}
                    <div class="form-group row my-2">
                        <label class="col-sm-2 col-form-label">
                            <b>数量</b>
                            <span class="badge badge-danger align-middle">必須</span>
                        </label>
                        <div class="col-sm-2">

                            <input type="text" name="detail[0][quantity]" value="{{ old('detail[0][quantity]') }}"
                                class="{{ $errors->has('detail.*.quantity') ? 'text-right input-quantity form-control is-invalid' : 'text-right input-quantity form-control' }}">

                            @error('detail.*.quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 担当者 --}}
                    <div class="form-group row my-2">
                        <label class="col-sm-2 col-form-label mr-1">
                            <b>担当者</b>
                            <span class="badge badge-danger align-middle">必須</span>
                        </label>
                        <div class="col-sm-6">
                            <div class="d-md-inline-flex w-100 row m-0">
                                {{-- 担当者 --}}
                                <select name="employee_id"
                                        class="custom-select custom-select-sm input-employee-select mr-md-5
                                        @if($errors->has('employee_id')) is-invalid @endif">
                                    <option value="">-----</option>
                                    @foreach (($input_items['employees'] ?? []) as $item)
                                        <option
                                            @if ($item['id'] == old('employee_id', $target_record_data['employee_id'] ?? null)) selected
                                            @endif
                                            value="{{ $item['id'] }}"
                                            data-code="{{ $item['code'] }}">
                                            {{-- TODO: コード用を作るか？ --}}
                                            {{ StringHelper::getNameWithId($item['code_zerofill'], $item['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row my-2">
                        <label class="col-sm-2 col-form-label mr-1">
                            <b>倉庫名</b>
                        </label>
                        <div class="col-sm-6">
                            <div class="d-md-inline-flex w-100 row m-0">
                                {{-- 倉庫名 --}}
                                <select name="warehouse_id"
                                        class="custom-select custom-select-sm input-warehouse-select mr-md-5"
                                        onchange="changeWarehouse()">
                                    <option value="">-----</option>
                                    @foreach (($input_items['warehouses'] ?? []) as $key => $item)
                                        <option
                                            @if ($item['id'] == old('warehouse_id')) selected
                                            @endif
                                            data-id="{{ $item['id'] }}"
                                            data-code="{{ $item['code'] }}"
                                            data-stock="{{ $item->getInventoryStock($product_data->id, $item['id']) }}"
                                            data-name="{{ $item['name'] }}">
                                            {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="stock_error d-none">
                                    <div class="invalid-feedback">※在庫がありません</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- エラー情報 --}}
                    <input type="hidden" name="errors-any" value="{{ $errors->any() }}" class="hidden-errors-any">
                    <input type="hidden" name="id" value="{{ old("id", $target_record_data->id ?? '') }}">
                    <input type="hidden" name="selected_warehouse_id" value="{{ old("warehouse_id", $warehouse_data->id) }}">
                    <input type="hidden" name="from_warehouse_id" value="{{old("from_warehouse_id", \App\Enums\InventoryType::INVENTORY_IN) }}">
                    <input type="hidden" name="to_warehouse_id" value="{{ old("to_warehouse_id", $warehouse_data->id) }}">
                    <input type="hidden" name="detail[0][product_id]" value="{{ old("detail[0][product_id]", $product_data->id) }}">
                    <input type="hidden" name="detail[0][product_name]" value="{{ old("detail[0][product_name]", $product_data->name) }}">

                    {{-- 在庫入出庫入力フラグ --}}
                    <input type="hidden" name="inout_page" value="1">

                    <div class="buttons-area text-center mt-4">
                        {{-- 一覧画面へ戻るボタン --}}
                        <a id="return" class="btn btn-primary back_active"
                        href="{{ session($session_inventory_key, route('inventory.inventory_stock_datas.index')) }}">
                            一覧画面へ戻る
                        </a>

                        {{-- 更新ボタン --}}
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
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Confirm Store Modal --}}
    @component('components.confirm_modal')
        @slot('modal_id', 'confirm-store')
        @slot('confirm_message', config('consts.message.common.confirm.store') )
        @slot('onclick_btn_ok', "store();return false;")
    @endcomponent

    {{-- JS読み込み --}}
    <script src="{{ mix('js/app/inventory/inventory_stock_datas/create_edit.js') }}"></script>

@endsection
