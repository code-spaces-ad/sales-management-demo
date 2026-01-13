/**
 * 商品コード変更時の処理
 */
window.changeProductCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-product-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 商品セレクトボックス変更処理
 */
window.changeProduct = function () {
    let code = $('.input-product-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された商品のコードをセット
    $('.input-product-code').val(code);
}

/**
 * 商品コード[start]変更時の処理
 */
window.changeStartProductCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-product-start-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 商品[start]セレクトボックス変更処理
 */
window.changeStartProduct = function () {
    let code = $('.input-product-start-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された商品のコードをセット
    $('.input-product-start-code').val(code);
}

/**
 * 商品コード[end]変更時の処理
 */
window.changeEndProductCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-product-end-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 商品[end]セレクトボックス変更処理
 */
window.changeEndProduct = function () {
    let code = $('.input-product-end-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された商品のコードをセット
    $('.input-product-end-code').val(code);
}
