/**
 * 月次指定クリアの処理
 * @param target
 */
window.clearInputMonth = function (target) {
    // 対象の月次指定をクリア
    $(target).closest('.form-group').find('.input-month').val('');
}

/**
 * 月次指定変更時の処理
 * @param target
 * @param billing_date
 */
window.changeInputMonth = function (target, billing_date = false) {
    let targetValueMonth = $(target).val();
    if (targetValueMonth === '') {
        return;
    }

    let tgtYear = targetValueMonth.substr(0, 4);
    let tgtMonth = targetValueMonth.substr(5, 2)

    // 月初日・月末日を取得
    let targetValueDateStart = new Date(tgtYear, tgtMonth - 1, 1);
    let targetValueDateEnd = new Date(tgtYear, tgtMonth, 0);

    // 日付を'0'埋めした文字列に変換
    let dateStart = targetValueDateStart.getFullYear() + '-' + ('0' + (targetValueDateStart.getMonth() + 1)).slice(-2) + '-' + ('0' + targetValueDateStart.getDate()).slice(-2);
    let dateEnd = targetValueDateEnd.getFullYear() + '-' + ('0' + (targetValueDateEnd.getMonth() + 1)).slice(-2) + '-' + ('0' + targetValueDateEnd.getDate()).slice(-2);

    if (billing_date) {
        $(target).closest('.form-group').find('.input-billing-date-start').val(dateStart);
        $(target).closest('.form-group').find('.input-billing-date-end').val(dateEnd);
        return true;
    }
    $(target).closest('.form-group').find("[class*='-date-start']").val(dateStart);
    $(target).closest('.form-group').find("[class*='-date-end']").val(dateEnd);
}
