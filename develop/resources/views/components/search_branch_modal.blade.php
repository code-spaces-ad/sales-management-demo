{{-- 支所検索用モーダルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

{{-- Search Branch Modal --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- モーダルヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title">支所検索</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- モーダル本体 --}}
            <div class="modal-body">
                <div class="col-md-10">
                    {{-- 支所名 --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>支所名</b>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="search_branch_name" id="search_branch_name" value="" class="form-control input-search-branch-name">
                        </div>
                    </div>
                </div>

                <div class="text-center mt-2 mb-2">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearSearchBranchInput();"
                            value="クリア">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>
                    <button type="submit" id="search_branch" value="検索" class="btn btn-primary">
                        <i class="fas fa-search"></i> 検索
                    </button>
                </div>

                <div class="col-md-12">
                    {{-- 支所テーブル --}}
                    <div class="table-responsive table-fixed" style="max-height: 300px;">
                        <table class="table table-bordered mt-2" id="branches_table">
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th style="width: 55%;">支所名</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($branches ?? [] as $key => $branch)
                                <tr>
                                    {{-- 支所名 --}}
                                    <td class="text-left">
                                        <a href="javascript:void(0);" data-branch-id="{{ $branch->id }}"
                                           onclick="{{ $onclick_select_branch }}">
                                            {{ StringHelper::getNameWithId($branch->customer_name, $branch->name) }}
                                        </a>
                                    </td>
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
            // 支所名にフォーカス
            clearSearchBranchInput();
            $('#search_branch_name').focus();
        });

        // 検索ボタンクリック
        $('#search_branch').on('click touchstart', function () {
            let searchBranchName = replaceKanaHalfToFull($('#search_branch_name').val());
            let reBranchName = new RegExp(searchBranchName);

            $("#branches_table tbody tr").each(function () {
                let txtBranchName = $(this).closest('tr').children('td')[0].innerText;

                if (txtBranchName.match(reBranchName) != null) {
                    $(this).show();
                    $(this).removeClass('inactive');
                } else {
                    $(this).hide();
                    $(this).addClass('inactive');
                }
            });

            // 支所テーブル行にCSS追加 ※背景色はcommon に合わせること。
            $("#branches_table tr:not(.inactive):even td").css("background-color", "#afeeee");
            $("#branches_table tr:not(.inactive):odd td").css("background-color", "#fff");
        });
    });
</script>

<script>
    /**
     * クリア処理
     */
    function clearSearchBranchInput() {
        $('.input-search-branch-name').val('');

        // 支所テーブルもすべて表示しておく
        $("#branches_table tbody tr").each(function () {
            $(this).show();
            $(this).removeClass('inactive');
        });

        // 得意先テーブル行の追加CSSを削除
        $("#branches_table tr:even td").css("background-color", "");
        $("#branches_table tr:odd td").css("background-color", "");
    }
</script>
