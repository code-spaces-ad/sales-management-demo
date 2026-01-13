/**
 * 仕入先コード変更時の処理
 */
window.changeSupplierCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-supplier-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-supplier-select').prop('selectedIndex', 0).change();
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 仕入先セレクトボックス変更処理
 */
window.changeSupplier = function () {
    let code = $('.input-supplier-select option:selected').data('code');

    function zeroPad(num, places) {
        let zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された仕入先のコードをセット
    $('.input-supplier-code').val(code);
}
