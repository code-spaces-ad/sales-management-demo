{{-- 得意先検索用モーダルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

{{-- Search Customer Modal --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- モーダルヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title">得意先検索</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- モーダル本体 --}}
            <div class="modal-body">
                <div class="col-md-10">
                    {{-- 得意先名 --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>得意先名</b>
                        </label>
                        <div class="col-sm-8">
                            
                            <input type="text" name="search_customer_name" id="search_customer_name" value="" class="form-control input-search-customer-name">

                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    {{-- 得意先名カナ --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>得意先名カナ</b>
                        </label>
                        <div class="col-sm-8">
                            
                            <input type="text" name="search_customer_name_kana" id="search_customer_name_kana" value="" class="form-control input-search-customer-name-kana">

                        </div>
                    </div>
                </div>

                <div class="text-center mt-2 mb-2">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearSearchCustomerInput();"
                            value="クリア">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>
                    
                    <button id="search_customer" class="btn btn-primary" type="submit" value="検索">
                        <i class="fas fa-search"></i> 検索
                    </button>

                </div>

                <div class="col-md-12">
                    {{-- 商品テーブル --}}
                    <div class="table-responsive table-fixed" style="max-height: 300px;">
                        <table class="table table-bordered mt-2" id="customers_table">
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th style="width: 15%;">コード</th>
                                <th style="width: 55%;">得意先名</th>
                                <th style="width: 30%;">得意先名カナ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($customers ?? [] as $key => $customer)
                                <tr>
                                    {{-- コード --}}
                                    <td class="text-center">
                                        <a href="javascript:void(0);" data-customer-id="{{ $customer->id }}"
                                           data-customer-code="{{ $customer->code }}"
                                           onclick="{{ $onclick_select_customer }}">
                                            {{ $customer->code_zerofill }}
                                        </a>
                                    </td>
                                    {{-- 得意先名 --}}
                                    <td class="text-left">{{ $customer->name }}</td>
                                    {{-- 得意先名カナ --}}
                                    <td class="text-left">{{ $customer->name_kana }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- モーダルフッター --}}
            {{-- ※フッターなし --}}
            {{--<div class="modal-footer">--}}
            {{--</div>--}}

        </div>
    </div>
</div>

<script>
    /**
     * ロードイベントに追加
     */
    window.addEventListener('load', function () {
        /**
         * モーダルのshownイベント
         */
        $('#{{ $modal_id }}').on('shown.bs.modal', function () {
            // 得意先名にフォーカス
            clearSearchCustomerInput();
            $('#search_customer_name').focus();
        });

        // 検索ボタンクリック
        $('#search_customer').on('click touchstart', function () {
            let searchCustomerName = replaceKanaHalfToFull($('#search_customer_name').val());
            let reCustomerName = new RegExp(searchCustomerName);
            let searchCustomerNameKana = replaceKanaHalfToFull($('#search_customer_name_kana').val());
            let reCustomerNameKana = new RegExp(searchCustomerNameKana);

            $("#customers_table tbody tr").each(function () {
                let txtCustomerName = $(this).closest('tr').children('td')[1].innerText;
                let txtCustomerNameKana = $(this).closest('tr').children('td')[2].innerText;

                if (txtCustomerName.match(reCustomerName) != null) {
                    if (txtCustomerNameKana.match(reCustomerNameKana) != null) {
                        $(this).show();
                        $(this).removeClass('inactive');
                    } else {
                        $(this).hide();
                        $(this).addClass('inactive');
                    }
                } else {
                    $(this).hide();
                    $(this).addClass('inactive');
                }
            });

            // 得意先テーブル行にCSS追加 ※背景色はcommon に合わせること。
            $("#customers_table tr:not(.inactive):even td").css("background-color", "#afeeee");
            $("#customers_table tr:not(.inactive):odd td").css("background-color", "#fff");
        });
    });
</script>

<script>
    /**
     * クリア処理
     */
    function clearSearchCustomerInput() {
        $('.input-search-customer-name').val('');
        $('.input-search-customer-name-kana').val('');

        // 得意先テーブルもすべて表示しておく
        $("#customers_table tbody tr").each(function () {
            $(this).show();
            $(this).removeClass('inactive');
        });

        // 得意先テーブル行の追加CSSを削除
        $("#customers_table tr:even td").css("background-color", "");
        $("#customers_table tr:odd td").css("background-color", "");
    }
</script>
