{{-- 倉庫検索用モーダルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

{{-- Search Warehouse Modal --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- モーダルヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title">倉庫検索</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- モーダル本体 --}}
            <div class="modal-body">
                <div class="col-md-10">
                    {{-- 倉庫名 --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>倉庫名</b>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="search_warehouse_name" id="search_warehouse_name" value="" class="form-control input-search-warehouse-name">
                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    {{-- 倉庫名カナ --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>倉庫名カナ</b>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="search_warehouse_name_kana" id="search_warehouse_name_kana" value="" class="form-control input-search-warehouse-name-kana">
                        </div>
                    </div>
                </div>

                <div class="text-center mt-2 mb-2">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearSearchWarehouseInput();"
                            value="クリア">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>
                    <button type="submit" id="search_warehouse" value="検索" class="btn btn-primary">
                        <i class="fas fa-search"></i> 検索
                    </button>
                </div>

                <div class="col-md-12">
                    {{-- 倉庫テーブル --}}
                    <div class="table-responsive table-fixed" style="max-height: 300px;">
                        <table class="table table-bordered mt-2" id="warehouses_table">
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th style="width: 16%;">コード</th>
                                <th style="width: 54%;">倉庫名</th>
                                <th style="width: 30%;">倉庫名カナ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($warehouses ?? [] as $key => $warehouse)
                                <tr>
                                    {{-- コード --}}
                                    <td class="text-center">
                                        <a href="javascript:void(0);" data-warehouse-id="{{ $warehouse->id }}"
                                           data-warehouse-code="{{ $warehouse->code }}"
                                           onclick="{{ $onclick_select_warehouse }}">
                                            {{ $warehouse->code_zerofill }}
                                        </a>
                                    </td>
                                    {{-- 倉庫名 --}}
                                    <td class="text-left">{{ $warehouse->name }}</td>
                                    {{-- 倉庫名カナ --}}
                                    <td class="text-left">{{ $warehouse->name_kana }}</td>
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
            // 倉庫名にフォーカス
            clearSearchWarehouseInput();
            $('#search_warehouse_name').focus();
        });

        // 検索ボタンクリック
        $('#search_warehouse').on('click touchstart', function () {
            let searchWarehouseName = replaceKanaHalfToFull($('#search_warehouse_name').val());
            let reWarehouseName = new RegExp(searchWarehouseName);
            let searchWarehouseNameKana = replaceKanaHalfToFull($('#search_warehouse_name_kana').val());
            let reWarehouseNameKana = new RegExp(searchWarehouseNameKana);

            $("#warehouses_table tbody tr").each(function () {
                let txtWarehouseName = $(this).closest('tr').children('td')[1].innerText;
                let txtWarehouseNameKana = $(this).closest('tr').children('td')[2].innerText;

                if (txtWarehouseName.match(reWarehouseName) != null) {
                    if (txtWarehouseNameKana.match(reWarehouseNameKana) != null) {
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

            // 倉庫テーブル行にCSS追加 ※背景色はcommon に合わせること。
            $("#warehouses_table tr:not(.inactive):even td").css("background-color", "#afeeee");
            $("#warehouses_table tr:not(.inactive):odd td").css("background-color", "#fff");
        });
    });
</script>

<script>
    /**
     * クリア処理
     */
    function clearSearchWarehouseInput() {
        $('.input-search-warehouse-name').val('');
        $('.input-search-warehouse-name-kana').val('');

        // 倉庫テーブルもすべて表示しておく
        $("#warehouses_table tbody tr").each(function () {
            $(this).show();
            $(this).removeClass('inactive');
        });

        // 倉庫テーブル行の追加CSSを削除
        $("#warehouses_table tr:even td").css("background-color", "");
        $("#warehouses_table tr:odd td").css("background-color", "");
    }
</script>
