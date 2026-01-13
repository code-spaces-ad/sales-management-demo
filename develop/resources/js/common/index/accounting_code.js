/**
 * 経理コード変更時の処理
 */
window.changeAccountingCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-accounting-code-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-accounting-code-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 経理コードセレクトボックス変更処理
 */
window.changeAccountingCodeSelect = function () {
    let code = $('.input-accounting-code-select option:selected').data('code');
    // 選択された経理のコードをセット
    $('.input-accounting-code').val(code);
}
