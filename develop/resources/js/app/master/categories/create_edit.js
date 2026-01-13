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

    if (!flgChangeForm && !flgEditRoute){
        searchAvailableNumber('categories');
    }

    // 単価のフォーカスがはずれた時
    $('.input-unit-price').blur(function () {
        changeUnitPrice();
    });

    $.fn.autoKana('input[name="name"] ', 'input[name="name_kana"]', {katakana: false});

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
window.getFloorValueForDigit = function(targetValue, digit) {
    let coef = Math.pow(10, digit);
    return Math.floor(targetValue * coef) / coef;
}
