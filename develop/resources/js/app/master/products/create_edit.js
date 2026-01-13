/**
 * フォーム変更フラグ
 * @type {boolean}
 */
let flgChangeForm = false;
/**
 * 新規/更新フラグ
 * @type {boolean}
 */
let flgEditRoute = false;

/**
 * 画面遷移アラート
 *
 * @returns {string}
 */
let unloadHandler = function (event) {
    if (flgChangeForm) {
        event.preventDefault();
    }
};

/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {

    /**
     * イベント監視開始
     */
    $(window).on('beforeunload', unloadHandler);
    /**
     * イベント監視解除
     */
    $('#editForm').on('submit', function () {
        $(window).off('beforeunload', unloadHandler);
    });
    /**
     * フォーム変更イベント
     */
    $('#editForm').on('change', function () {
        flgChangeForm = true;
    });

    // コードにフォーカス
    $('.input-code').focus();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;
    flgEditRoute = $('.hidden-is-edit-route').val() ? true: false;

    // 単価のフォーカスがはずれた時
    $('.input-unit-price').blur(function () {
        changeUnitPrice();
    });

    if (!flgChangeForm && !flgEditRoute){
        searchAvailableNumber('products');
    }

    // カテゴリチェンジ　イベント発火
    changeCategory();

    $.fn.autoKana('input[name="name"] ', 'input[name="name_kana"]', {katakana: false});

    let rate = $('input[name="consumption_tax_rate"]').val();

    // 税率の切り替え処理
    $('input[name="reduced_tax_flag"]').on('change', function (e) {
        $('input[name="consumption_tax_rate"]').val($(this).data('tax-rate'));
    });

    // 表示の切り替え処理
    $('input[name="tax_type_id"]').on('change', function (e) {
        if ($(this).val() === String(window.Laravel.enums.tax_type.tax_exempt)) {
            $('#reduced_tax_flag').hide();
            $('input[name="reduced_tax_flag"][value="0"]').prop('checked', true);
            $('input[name="consumption_tax_rate"]').val(0);
        }
        if ($(this).val() !== String(window.Laravel.enums.tax_type.tax_exempt)) {
            $('#reduced_tax_flag').show();
            let taxRate = $('input[name="reduced_tax_flag"]:checked').data('tax-rate');
            $('input[name="consumption_tax_rate"]').val(taxRate);
        }
    });

    // 税区分チェンジ　イベント発火
    $('input[name="tax_type_id"]:checked').change();
    $('input[name="consumption_tax_rate"]').val(rate);
});

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();     // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-code').val('');
    $('.input-name').val('');
    $('.input-name-kana').val('');
    $('.input-unit-price').val('0');

    $('.input-unit-id-select').prop('selectedIndex', 0).change();
    $('.input-unit-price-decimal-digit-select').prop('selectedIndex', 0).change();
    $('.input-quantity-decimal-digit-select').prop('selectedIndex', 0).change();
}

window.changeUnitPrice = function () {
    let digit = $('.input-unit-price-decimal-digit-select option:selected').val();
    let unitPrice = $('.input-unit-price').val();

    let unitPriceFloor = getFloorValueForDigit(unitPrice, digit);
    $('.input-unit-price').val(unitPriceFloor);
}

/**
 * 桁数で切り捨てされた値を取得
 * @param targetValue
 * @param digit
 */
window.getFloorValueForDigit = function (targetValue, digit) {
    let coef = Math.pow(10, digit);
    return Math.floor(targetValue * coef) / coef;
}

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

/**
 * 仕入先コード変更時の処理
 * @param target
 */
window.changeSupplierCodeCreateEdit = function (target) {
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

    // 仕入先セレクトボックス変更処理
    changeSupplierCreateEdit();
}

/**
 * 仕入先セレクトボックス変更処理
 */
window.changeSupplierCreateEdit = function () {
    let code = $('.input-supplier-select option:selected').data('code');
    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }
    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された仕入先名のコードをセット
    $('.input-supplier-code').val(code);
}

