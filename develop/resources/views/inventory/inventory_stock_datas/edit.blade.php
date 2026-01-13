{{-- 在庫調整編集画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline = config('consts.title.inventory.menu.stock_datas.edit');
    $next_url = route('inventory.inventory_stock_datas.update', $target_record_data);
    $next_btn_text = '更新';
    $method = 'PUT';
    $is_edit_route = true;
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
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">
                            <b>コード</b>
                        </label>
                        <div class="col-sm-2">
                            {{ $target_record_data->mWarehouse->code_zerofill }}

                        </div>
                    </div>

                    {{-- 倉庫名 --}}
                    <div class="form-group row my-1">
                        <label class="col-sm-2 col-form-label">
                            <b>倉庫名</b>
                        </label>
                        <div class="col-sm-6">
                            {{ $target_record_data->mWarehouse->name }}
                        </div>
                    </div>

                    {{--在庫数_データ--}}
                    <div class="result-table-area table-responsive table-fixed" style="max-height: none !important;">
                        <table class="table table-bordered table-responsive-org">
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th style="width: 20%">商品名</th>
                                <th style="width: 20%">在庫数量</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div style="font-size: 1.2em;">
                                        {{ $target_record_data->mProduct->name }}
                                    </div>
                                </td>

                                <td>

                                    <input type="text" name="inventory_stocks"
                                        value="{{ old('inventory_stocks', $target_record_data->inventory_stocks ?? '') }}"
                                        class="text-right form-control input-name input-quantity{{ $errors->has('inventory_stocks') ? ' is-invalid' : '' }}" >
                                    <input type="hidden" name="id" value="{{ old("id", $target_record_data->id ?? '') }}">
                                    <input type="hidden" name="warehouse_name" value="{{ old("warehouse_name", $target_record_data->mWarehouse->name ?? '') }}">
                                    <input type="hidden" name="product_name" value="{{ old("product_name", $target_record_data->mProduct->name ?? '') }}">

                                    @error('inventory_stocks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="buttons-area text-center mt-4">
                        {{-- 一覧画面へ戻るボタン --}}
                        <a id="return" class="btn btn-primary back_active"
                        href="{{ session($session_inventory_key, route('inventory.inventory_stock_datas.index')) }}">
                            一覧画面へ戻る
                        </a>

                        {{-- 登録ボタン、更新ボタン --}}
                        <input type="submit" value="{{ $next_btn_text }}" class="btn btn-primary" id="btn_submit" style="display:none;">

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

    @if ($is_edit_route)
        <form name="deleteForm" id="deleteForm" method="POST">
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

    <script>
        /**
         * フォーム変更フラグ
         * @type {boolean}
         */
        let flgChangeForm = false;

        /**
         * 画面遷移アラート
         *
         * @returns {string}
         */
        let unloadHandler = function () {
            if (flgChangeForm === false) {
                // 変更なしの場合は、アラートを表示しない
                return;
            }

            return '';
        };

        /**
         * ロードイベントに追加
         */
        window.addEventListener('load', function () {
            /**
             * イベント監視開始
             */
            $(window).on('beforeunload', unloadHandler);
            /**
             * イベント監視解除
             */
            $('#editForm').on('submit', function () {
                $(window).off('beforeunload', unloadHandler);
            });
            /**
             * フォーム変更イベント
             */
            $('#editForm').on('change', function () {
                flgChangeForm = true;
            });

            // コードにフォーカス
            $('.input-code').focus();

            // 初期化
            flgChangeForm = {{ $errors->any() ? 'true' : 'false' }};

            /**
             * 数量 フォーカスイベント
             */
            $(".input-quantity").focus(function () {
                // 桁区切りを一旦解除
                let quantity = $(this).val().replace(/,/g, '');
                $(this).val(quantity);

                // type変更
                $(this).get(0).type = 'number';

                // 全選択にする
                $(this).select();
            });

            /**
             * 数量 キーダウンイベント
             */
            $(".input-quantity").keydown(function (event) {
                // 「type='number'」は「e」が入力可能なので除外
                if (event.key === 'e') {
                    return false;
                }
            });

            /**
             * 数量 ブラーイベント
             */
            $(".input-quantity").blur(function () {
                let quantityStr = $(this).val().replace(/[^\.0-9]/g, '');   // ※マイナス値不可
                if (quantityStr.length === 0) {
                    quantityStr = '0';
                }

                let quantity = parseFloat(quantityStr);
                let digit = 0;
                let method = 3;
                let calcQuantity = getFloorValueForDigit(quantity, digit, method);

                // typeを元に戻す
                $(this).get(0).type = 'text';
                // 数量セット
                $(this).val(calcQuantity.toLocaleString(undefined, {
                    minimumFractionDigits: digit,
                    maximumFractionDigits: digit
                }));

            });

        });

        /**
         * 桁数で切り捨てされた値を取得
         *
         * @param targetValue
         * @param digit
         * @param method
         * @returns {number|*}
         */
        function getFloorValueForDigit(targetValue, digit, method) {
            if (typeof digit === 'undefined') {
                return targetValue;
            }

            let coef = Math.pow(10, digit);
            // 切り捨て
            if (method === {{ RoundingMethodType::ROUND_DOWN }}) {
                return Math.floor(targetValue * coef) / coef;
            }
            // 切り上げ
            if (method === {{ RoundingMethodType::ROUND_UP }}) {
                return Math.ceil(targetValue * coef) / coef;
            }
            // 四捨五入
            if (method === {{ RoundingMethodType::ROUND_OFF }}) {
                return Math.round(targetValue * coef) / coef;
            }
            return targetValue;
        }
    </script>

    <script>
        /**
         * 登録更新処理
         */
        function store() {
            // モーダル閉じる
            $('#confirm-store').modal('hide');

            // ボタン非活性
            disableButtons();
            // ローディング切替
            changeStoreLoading();

            // submit処理
            $('#editForm').submit();
        }

        /**
         * 削除処理
         */
        function destory() {
            // モーダル閉じる
            $('#confirm-delete').modal('hide');

            // ボタン非活性
            disableButtons();
            // ローディング切替
            changeDeleteLoading();

            // submit処理
            $('#deleteForm').submit();
        }

        /**
         * ボタン非活性
         */
        function disableButtons() {
            // ボタン非活性
            $('#store').prop('disabled', true);
            $('#clear').prop('disabled', true);
            $('#delete').prop('disabled', true);
            $('#return').prop('disabled', true);
        }

        /**
         * ローディング切替(登録・更新）
         */
        function changeStoreLoading() {
            // ローディング切替
            $('#store i').hide();
            $('#store div').show();
        }

        /**
         * ローディング切替(削除）
         */
        function changeDeleteLoading() {
            // ローディング切替
            $('#delete i').hide();
            $('#delete div').show();
        }
    </script>
@endsection
