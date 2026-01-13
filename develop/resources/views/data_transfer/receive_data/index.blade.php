{{-- データ送信用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    use Carbon\Carbon;

    $headline = config('consts.title.data_transfer.menu.receive_data');
    // デフォルトMAX日付
    $default_max_date = config('consts.default.common.default_max_date');
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div id="loading" style="display: none;">
        <div class='loadingMsg'>処理中...</div>
    </div>

    <div class="row">
        <form name="searchForm" id="searchForm" action="" method="GET" class="col-12 px-0 px-md-4 pb-md-2">
            {{-- 検索項目 --}}
            <div class="card">
                <div class="card-body">
                    {{-- 受信種別 --}}
                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        <label class="col-md-4 col-form-label pl-0 pb-md-3">
                            <b>受信種別</b>
                            <span class="badge badge-danger">必須</span>
                        </label>
                        <div class="d-md-inline-flex col-md-10 pr-md-0">
                            <select name="pos_receive_api_id"
                                    class="custom-select input-pos-receive-api-id-select mr-md-1 clear-select"
                                    onchange="changeUrlSelectPosReceiveApiId(this);"
                                    @if($errors->has('pos_receive_api_id')) is-invalid @endif>
                                <option value="">-----</option>
                                @foreach (($search_items['pos_receive_api_id'] ?? []) as $key => $item)
                                    <option
                                        @if ($key == old('pos_receive_api_id', $search_condition_input_data['pos_receive_api_id'] ?? null))
                                        selected
                                        @endif
                                        value="{{ $key }}"
                                        data-url="{{ $search_items['pos_receive_api_url'][$key] }}">
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pos_receive_api_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- URL --}}
                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        <label class="col-md-4 col-form-label pl-0 pb-md-3">
                            <b>URL</b>
                        </label>
                        <div class="d-md-inline-flex col-md-9 pr-md-0">
                            <input type="text"
                                   name="url"
                                   value="{{ $search_condition_input_data['url'] ?? '' }}"
                                   class="{{ $errors->has('url') ? 'form-control pos-api-input-url is-invalid' : 'form-control pos-api-input-url' }}"
                                   readonly>
                            @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 対象日付 --}}
                    @include('common.index.target_date')

                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        {{-- 店舗ID --}}
                        <label class="col-md-4 col-form-label pl-0 pb-md-3">
                            <b>店舗ID (store_id)</b>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="store_id"
                                   value="{{ old('store_id', $target_record_data['store_id'] ?? '') }}"
                                   class="form-control input-store-id{{ $errors->has('store_id') ? ' is-invalid' : '' }}">
                            @error('store_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        {{-- レジID --}}
                        <label class="col-md-4 col-form-label pl-0 pb-md-3">
                            <b>レジID (regi_id)</b>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="regi_id"
                                   value="{{ old('regi_id', $target_record_data['regi_id'] ?? '') }}"
                                   class="form-control input-regi-id{{ $errors->has('regi_id') ? ' is-invalid' : '' }}">
                            @error('regi_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        {{-- 棚卸ステータス --}}
                        <label class="col-md-4 col-form-label pl-0 pb-md-3">
                            <b>棚卸ステータス (inventory_status)</b>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" name="inventory_status"
                                   value="{{ old('inventory_status', $target_record_data['inventory_status'] ?? '') }}"
                                   class="form-control input-regi-id{{ $errors->has('inventory_status') ? ' is-invalid' : '' }}">
                            @error('inventory_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- パラメータ(json) --}}
                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        <label class="col-form-label col-md-4 pl-0 pb-md-3">
                            <b>パラメータ(json)</b>
                        </label>
                        <div class="col-md-12 pr-md-0">
                            <div class="d-md-inline-flex col-12 px-0">
                                <div class="col-md-12 pl-0 pl-md-1">
                                    <textarea name="input_param_json"
                                              class="form-control pos-api-input-param-json{{ $errors->has('input_param_json') ? ' is-invalid' : '' }}"
                                              rows="5" readonly>{{ old('input_param_json', $target_record_data['input_param_json'] ?? '') }}</textarea>
                                </div>
                            </div>
                            @error('input_param_json')
                            <div class="invalid-feedback ml-3">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 戻り値(json) --}}
                    <div class="form-group d-md-inline-flex col-md-6 my-1">
                        <label class="col-form-label col-md-4 pl-0 pb-md-3">
                            <b>戻り値(json)</b>
                        </label>
                        <div class="col-md-12 pr-md-0">
                            <div class="d-md-inline-flex col-12 px-0">
                                <div class="col-md-12 pl-0 pl-md-1">
                                    <textarea name="return_value"
                                              class="form-control pos-api-return-value{{ $errors->has('return_value') ? ' is-invalid' : '' }}"
                                              rows="15" readonly>{{ old('return_value', $target_record_data['return_value'] ?? '') }}</textarea>
                                </div>
                            </div>
                            @error('return_value')
                            <div class="invalid-feedback ml-3">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="col-md-12 px-0">
                        <div class="text-center">
                            <a class="btn btn-primary" onclick="posReceiveDataApi();">
                                <i class="fas fa-search"></i> 送信
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
