{{-- 担当者検索用モーダルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

{{-- Search Employee Modal --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- モーダルヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title">担当者検索</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- モーダル本体 --}}
            <div class="modal-body">
                <div class="col-md-10">
                    {{-- 担当者名 --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>担当者名</b>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" id="search_employee_name" name="search_employee_name" value="" class="form-control input-search-employee-name">
                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    {{-- 担当者名カナ --}}
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <b>担当者名カナ</b>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" id="search_employee_name_kana" name="search_employee_name_kana" value="" class="form-control input-search-employee-name-kana">
                        </div>
                    </div>
                </div>

                <div class="text-center mt-2 mb-2">
                    <button type="button" class="btn btn-secondary mr-2" onclick="clearSearchEmployeeInput();"
                            value="クリア">
                        <i class="fas fa-times"></i>
                        クリア
                    </button>
                    <button type="submit" id="search_employee" class="btn btn-primary" value="検索">
                        <i class="fas fa-search"></i> 検索
                    </button>
                </div>

                <div class="col-md-12">
                    {{-- 担当者テーブル --}}
                    <div class="table-responsive table-fixed" style="max-height: 300px;">
                        <table class="table table-bordered mt-2" id="employees_table">
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th style="width: 16%;">コード</th>
                                <th style="width: 54%;">担当者名</th>
                                <th style="width: 30%;">担当者名カナ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($employees ?? [] as $key => $employee)
                                <tr>
                                    {{-- コード --}}
                                    <td class="text-center">
                                        <a href="javascript:void(0);" data-employee-id="{{ $employee->id }}"
                                           data-employee-code="{{ $employee->code }}"
                                           onclick="{{ $onclick_select_employee }}">
                                            {{ $employee->code_zerofill }}
                                        </a>
                                    </td>
                                    {{-- 担当者名 --}}
                                    <td class="text-left">{{ $employee->name }}</td>
                                    {{-- 担当者名カナ --}}
                                    <td class="text-left">{{ $employee->name_kana }}</td>
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
            // 担当者名にフォーカス
            $('#search_employee_name').focus();
            clearSearchEmployeeInput();
        });

        // 検索ボタンクリック
        $('#search_employee').on('click touchstart', function () {
            let searchEmployeeName = replaceKanaHalfToFull($('#search_employee_name').val());
            let reEmployeeName = new RegExp(searchEmployeeName);
            let searchEmployeeNameKana = replaceKanaHalfToFull($('#search_employee_name_kana').val());
            let reEmployeeNameKana = new RegExp(searchEmployeeNameKana);

            $("#employees_table tbody tr").each(function () {
                let txtEmployeeName = $(this).closest('tr').children('td')[1].innerText;
                let txtEmployeeNameKana = $(this).closest('tr').children('td')[2].innerText;

                if (txtEmployeeName.match(reEmployeeName) != null) {
                    if (txtEmployeeNameKana.match(reEmployeeNameKana) != null) {
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

            // 担当者テーブル行にCSS追加 ※背景色はcommon に合わせること。
            $("#employees_table tr:not(.inactive):even td").css("background-color", "#afeeee");
            $("#employees_table tr:not(.inactive):odd td").css("background-color", "#fff");
        });
    });
</script>

<script>
    /**
     * クリア処理
     */
    function clearSearchEmployeeInput() {
        $('.input-search-employee-name').val('');
        $('.input-search-employee-name-kana').val('');

        // 担当者テーブルもすべて表示しておく
        $("#employees_table tbody tr").each(function () {
            $(this).show();
            $(this).removeClass('inactive');
        });

        // 担当者テーブル行の追加CSSを削除
        $("#employees_table tr:even td").css("background-color", "");
        $("#employees_table tr:odd td").css("background-color", "");
    }
</script>
