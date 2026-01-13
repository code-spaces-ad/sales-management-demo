/**
 * 集計グループコード変更時の処理
 */
window.changeSummaryGroupCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-summary-group-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-summary-group-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 集計グループセレクトボックス変更処理
 */
window.changeSummaryGroup = function () {
    let code = $('.input-summary-group-select option:selected').data('code');
    // 選択された集計グループのコードをセット
    $('.input-summary-group-code').val(code);
}
