/**
 * 倉庫コード変更時の処理
 */
window.changeWarehouseCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-warehouse-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    }
}

/**
 * 倉庫セレクトボックス変更処理
 */
window.changeWarehouse = function () {
    let code = $('.input-warehouse-select option:selected').data('code');
    // 選択された倉庫のコードをセット
    $('.input-warehouse-code').val(code);
}
