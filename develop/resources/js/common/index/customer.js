/**
 * 得意先コード変更時の処理
 */
window.changeCustomerCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-customer-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $(target).val('');  // コード枠はクリア
        $('.input-customer-select').prop('selectedIndex', 0).change();
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 得意先セレクトボックス変更処理
 */
window.changeCustomer = function () {
    let code = $('.input-customer-select option:selected').data('code');

    function zeroPad(num, places) {
        let zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された得意先名のコードをセット
    $('.input-customer-code').val(code);

    // 支所セレクトボックスフィルタリング
    filterBranch();
}

/**
 * 得意先コード[start]変更時の処理
 */
window.changeStartCustomerCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-customer-start-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $(target).val('');  // コード枠はクリア
        $('.input-customer-start-select').prop('selectedIndex', 0).change();
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 得意先[start]セレクトボックス変更処理
 */
window.changeStartCustomer = function () {
    let code = $('.input-customer-start-select option:selected').data('code');

    function zeroPad(num, places) {
        let zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された得意先名のコードをセット
    $('.input-customer-start-code').val(code);

    // 支所セレクトボックスフィルタリング
    filterBranch();
}

/**
 * 得意先[end]コード変更時の処理
 */
window.changeEndCustomerCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-customer-end-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $(target).val('');  // コード枠はクリア
        $('.input-customer-end-select').prop('selectedIndex', 0).change();
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 得意先[end]セレクトボックス変更処理
 */
window.changeEndCustomer = function () {
    let code = $('.input-customer-end-select option:selected').data('code');

    function zeroPad(num, places) {
        let zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された得意先名のコードをセット
    $('.input-customer-end-code').val(code);

    // 支所セレクトボックスフィルタリング
    filterBranch();
}
