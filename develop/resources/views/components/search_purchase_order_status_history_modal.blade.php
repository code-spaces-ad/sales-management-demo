{{-- 発注伝票状態履歴検索用モーダルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

{{-- Search Purchase Order Status History Modal --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- モーダルヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title">状態履歴検索</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- モーダル本体 --}}
            <div class="modal-body">
                <div class="col-md-10">
                    {{-- 状態 --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>状態</b>
                        </label>
                        <div class="col-sm-8">
                            
                            <input type="text" name="search_purchase_order_status" id="search_purchase_order_status" value=""
                                class="form-control input-search-purchase-order-status" />

                        </div>
                    </div>

                    {{-- 更新者 --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>更新者</b>
                        </label>
                        <div class="col-sm-8">
                            
                            <input type="text" name="search_updated_user_name" id="search_updated_user_name" value=""
                                class="form-control input-search-updated-user-name" />

                        </div>
                    </div>
                </div>

                <div class="text-center mt-2 mb-2">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearSearchPurchaeOrderInput();" value="クリア">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>
                    <button type="submit" id="search_purchase_order" value="検索" class="btn btn-primary">
                        <i class="fas fa-search"></i> 検索
                    </button>
                </div>

                <div class="col-md-12">
                    {{-- 検索結果テーブル --}}
                    <div class="table-responsive table-fixed" style="max-height: 300px;">
                        <table class="table table-bordered mt-2" id="purchase_order_table">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th style="width: 40%;">変更日時</th>
                                    <th style="width: 20%;">状態</th>
                                    <th style="width: 40%;">更新者</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($status_history ?? [] as $key => $item)
                                    <tr>
                                        {{-- 変更日時 --}}
                                        <td class="text-center">{{ $item->created_at_slash }}</td>
                                        {{-- 状態 --}}
                                        <td class="text-center">{{ $item->order_status_name }}</td>
                                        {{-- 更新者 --}}
                                        <td class="text-left">{{ $item->updated_user_name }}</td>
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
            // 状態にフォーカス
            $('#search_purchase_order_status').focus();
        });

        // 検索ボタンクリック
        $('#search_purchase_order').click(function () {
            let searchPurchaseOrderStatus = $('#search_purchase_order_status').val();
            let rePurchaseOrderStatus = new RegExp(searchPurchaseOrderStatus);
            let searchUpdatedUserName = replaceKanaHalfToFull($('#search_updated_user_name').val());
            let reUpdatedUserName = new RegExp(searchUpdatedUserName);

            $("#purchase_order_table tbody tr").each(function(){
                let txtPurchaseOrderStatus = $(this).closest('tr').children('td')[1].innerText;
                let txtUpdatedUserName = $(this).closest('tr').children('td')[2].innerText;

                if (txtPurchaseOrderStatus.match(rePurchaseOrderStatus) != null) {
                    if (txtUpdatedUserName.match(reUpdatedUserName) != null) {
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

            // 検索結果テーブル行にCSS追加 ※背景色はcommon に合わせること。
            $("#purchase_order_table tr:not(.inactive):even td").css("background-color", "#afeeee");
            $("#purchase_order_table tr:not(.inactive):odd td").css("background-color", "#fff");
        });
    });
</script>

<script>
    /**
     * クリア処理
     */
    function clearSearchPurchaeOrderInput() {
        $('.input-search-purchase-order-status').val('');
        $('.input-search-updated-user-name').val('');

        // 検索結果テーブルもすべて表示しておく
        $("#purchase_order_table tbody tr").each(function(){
            $(this).show();
            $(this).removeClass('inactive');
        });

        // 検索結果テーブル行の追加CSSを削除
        $("#purchase_order_table tr:even td").css("background-color", "");
        $("#purchase_order_table tr:odd td").css("background-color", "");
    }
</script>
