/**
 * 部門コード変更時の処理
 */
window.changeDepartmentCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-department-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $(target).val('');  // コード枠はクリア
        $('.department').prop('selectedIndex', 0).change();
    }

    // select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 部門セレクトボックス変更処理
 */
window.changeDepartment = function () {
    let code = $('.input-department-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 4)
    }
    // 選択された部門のコードをセット
    $('.input-department-code').val(code);

    $('.input-office-facilities-select').prop('disabled', false);

    if (!code) {
        $('.input-office-facilities-select').prop('disabled', true);
    }

    // 事業所セレクトボックスフィルタリング（hidden判定で切替）
    window.selectByOfficeFacility();
}
