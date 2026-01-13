{{-- 商品検索用モーダルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

{{-- Search Product Modal --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- モーダルヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title">商品検索</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- モーダル本体 --}}
            <div class="modal-body">
                <div class="col-md-10">
                    {{-- 商品名 --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>商品名</b>
                        </label>
                        <div class="col-sm-8">
                            
                            <input type="text" name="search_product_name" id="search_product_name" value="" class="form-control input-search-product-name">

                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    {{-- 商品名カナ --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>商品名カナ</b>
                        </label>
                        <div class="col-sm-8">
                            
                            <input type="text" name="search_product_name_kana" id="search_product_name_kana" value="" class="form-control input-search-product-name-kana">

                        </div>
                    </div>
                </div>

                <div class="text-center mt-2 mb-2">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearSearchProductInput();"
                            value="クリア">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>

                    <button id="search_product" class="btn btn-primary" type="submit" value="検索">
                        <i class="fas fa-search"></i> 検索
                    </button>
                    
                </div>

                <div class="col-md-12">
                    {{-- 商品テーブル --}}
                    <div class="table-responsive table-fixed" style="max-height: 300px;">
                        <table class="table table-bordered mt-2" id="products_table">
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th style="width: 16%;">コード</th>
                                <th style="width: 54%;">商品名</th>
                                <th style="width: 30%;">商品名カナ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($products ?? [] as $key => $product)
                                <tr>
                                    {{-- コード --}}
                                    <td class="text-center">
                                        <a href="javascript:void(0);" data-product-id="{{ $product->id }}"
                                           data-product-code="{{ $product->code }}"
                                           onclick="{{ $onclick_select_product }}">
                                            {{ $product->code_zerofill }}
                                        </a>
                                    </td>
                                    {{-- 商品名 --}}
                                    <td class="text-left">{{ $product->name }}</td>
                                    {{-- 商品名カナ --}}
                                    <td class="text-left">{{ $product->name_kana }}</td>
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
            // 商品名にフォーカス
            $('#search_product_name').focus();
            clearSearchProductInput();
        });

        // 検索ボタンクリック
        $('#search_product').on('click touchstart', function () {
            let searchProductName = replaceKanaHalfToFull($('#search_product_name').val());
            let reProductName = new RegExp(searchProductName);
            let searchProductNameKana = replaceKanaHalfToFull($('#search_product_name_kana').val());
            let reProductNameKana = new RegExp(searchProductNameKana);

            $("#products_table tbody tr").each(function () {
                let txtProductName = $(this).closest('tr').children('td')[1].innerText;
                let txtProductNameKana = $(this).closest('tr').children('td')[2].innerText;

                if (txtProductName.match(reProductName) != null) {
                    if (txtProductNameKana.match(reProductNameKana) != null) {
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

            // 商品テーブル行にCSS追加 ※背景色はcommon に合わせること。
            $("#products_table tr:not(.inactive):even td").css("background-color", "#afeeee");
            $("#products_table tr:not(.inactive):odd td").css("background-color", "#fff");
        });
    });
</script>

<script>
    /**
     * クリア処理
     */
    function clearSearchProductInput() {
        $('.input-search-product-name').val('');
        $('.input-search-product-name-kana').val('');

        // 商品テーブルもすべて表示しておく
        $("#products_table tbody tr").each(function () {
            $(this).show();
            $(this).removeClass('inactive');
        });

        // 商品テーブル行の追加CSSを削除
        $("#products_table tr:even td").css("background-color", "");
        $("#products_table tr:odd td").css("background-color", "");
    }
</script>
