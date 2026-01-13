{{-- ダッシュボード画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('layouts.base')
@extends('layouts.head')
@extends('layouts.sidebar')
@extends('layouts.header')
@extends('layouts.footer')

@php
    $headline      = 'ダッシュボード';
    $next_url      = route('dashboard.store');
    $next_btn_text = '登録';
    $method        = 'POST';
    $is_edit_route = false;
@endphp

@section('title', $headline . ' | ' . config('app.name'))
@section('headline', $headline)

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="input-area">
                <div>
                    <div class="form-group col-md-8 d-none">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header"
                                     style="color: white; background-color: #1b4b72; font-weight: bold">
                                    種別累計売上表
                                </div>
                                <div class="card-body">
                                    <canvas id="lineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-8">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header"
                                     style="color: white; background-color: #1b4b72; font-weight: bold">
                                    共有メモ
                                </div>
                                <div class="card-body">
                                    <form name="editForm" id="editForm" action="{{ $next_url }}" method="POST" enctype="multipart/form-data">
                                    @method($method)
                                    @csrf

                                    {{-- 共有メモ --}}
                                    <div class="col-sm-12">
                                        <textarea name="news"class="form-control input-news{{ $errors->has('news') ? ' is-invalid' : '' }}">{{ old('news', $target_record_data['news'] ?? null) }}</textarea>
                                        @error('news')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="buttons-area text-center mt-4">
                                        {{-- 登録ボタン、更新ボタン --}}
                                        <button type="submit" class="btn btn-primary" id="btn_submit" style="display:none;">
                                            {{ $next_btn_text }}
                                        </button>
                                        <button type="button" id="store"
                                                class="btn btn-primary"
                                                onclick="update();return false;">
                                            <i class="far fa-edit"></i>
                                            <div class="spinner-border spinner-border-sm text-light align-middle"
                                                 role="status"
                                                 style="display: none;">
                                            </div>
                                            {{$next_btn_text}}
                                        </button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group col-md-12 d-md-inline-flex">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="result-table-area table-responsive table-fixed"
                                     style="max-height: none !important;">
                                    <div class="card-header"
                                         style="color: white; background-color: #1b4b72; font-weight: bold">
                                        <div class="d-md-inline-flex">
                                            請求締 未実施&nbsp;&nbsp;(対象件数：{{number_format(count($unclosing_data))}}件)
                                        </div>
                                    </div>
                                    <div class="overflow-auto" style="height:300px;">
                                        <div class="card-body">
                                            <!-- 未締一覧 -->
                                            <table class="table table-bordered table-responsive-org mb-1"
                                                   id="order_products_table">
                                                <thead class="thead-light text-center">
                                                <th style="width: 10%;">対象年月</th>
                                                <th style="width: 10%;">締日</th>
                                                <th style="width: 45%;">得意先名</th>
                                                <th style="width: 10%;">件数</th>
                                                </thead>
                                                <tbody>
                                                @if( count($unclosing_data) === 0 )
                                                    <tr>
                                                        <td colspan="4" class="text-left align-middle">
                                                            <div class="pl-2" style="color: green;">
                                                                <i class="far fa-check-circle"></i>
                                                                現在、未締めの伝票はありません。
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @foreach ($unclosing_data as $key => $detail)
                                                        <tr>
                                                            <td class="text-center align-middle">
                                                                @if (isset($unclosing_data[$key - 1]))
                                                                    @if ($unclosing_data[$key - 1]->closing_ym !== $detail->closing_ym )
                                                                        <a href="{{ route('invoice.charge_closing.index',
                                                                            [
                                                                                'charge_date' => substr($detail->closing_ym, 0, 4).'-'.substr($detail->closing_ym, 4, 2) ?? '',
                                                                                'closing_date' => $detail->closing_date,
                                                                            ])
                                                                        }}">
                                                                            {{$detail->closing_ym}}
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                    <a href="{{ route('invoice.charge_closing.index',
                                                                        [
                                                                            'charge_date' => substr($detail->closing_ym, 0, 4).'-'.substr($detail->closing_ym, 4, 2) ?? '',
                                                                            'closing_date' => $detail->closing_date,
                                                                        ])
                                                                    }}">
                                                                        {{$detail->closing_ym}}
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                {{config('consts.default.common.closing_date_list')[$detail->closing_date]}}
                                                                日締め
                                                            </td>
                                                            <td class="text-legt align-middle">
                                                                <a href="{{ route('invoice.charge_closing.index',
                                                                    [
                                                                        'charge_date' => substr($detail->closing_ym, 0, 4).'-'.substr($detail->closing_ym, 4, 2) ?? '',
                                                                        'closing_date' => $detail->closing_date,
                                                                        'customer_id' => $detail->mCustomer->id,
                                                                    ])
                                                                }}">
                                                                    {{ StringHelper::getNameWithId($detail->mCustomer->code_zerofill, $detail->mCustomer->name) }}
                                                                </a>
                                                            </td>
                                                            <td class="text-right align-middle">{{$detail->order_count}}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card d-none">
                                <div class="card-header"
                                     style="color: white; background-color: #1b4b72; font-weight: bold">
                                    &nbsp;今後、ダッシュボード表示が何かあれば表示する。上の「d-none」外す
                                </div>
                                <div class="overflow-auto" style="height:300px;">
                                    <div class="card-body">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Window Onload --}}
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

        });
    </script>

    <script>
        /**
         * 登録更新処理
         */
        function update() {
            // ボタン非活性
            disableButtons();
            // ローディング切替
            changeStoreLoading();

            // submit処理
            $('#editForm').submit();
        }

        /**
         * ボタン非活性
         */
        function disableButtons() {
            // ボタン非活性
            $('#store').prop('disabled', true);
        }

        /**
         * ローディング切替(登録・更新）
         */
        function changeStoreLoading() {
            // ローディング切替
            $('#store i').hide();
            $('#store div').show();
        }
    </script>

    <script src="{{ mix('js/loadBefore.js') }}"></script>

    <script>
        let lineCtx = document.getElementById("lineChart");
        // 線グラフの設定
        let lineChart = new Chart(lineCtx, @json($categories));
    </script>
@endsection
