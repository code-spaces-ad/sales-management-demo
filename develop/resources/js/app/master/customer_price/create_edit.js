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
        event.returnValue = '';
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
    flgEditRoute = $('.hidden-is-edit-route').val() ? true : false;

    if (!flgChangeForm && !flgEditRoute) {
        searchAvailableNumber('customer_price');
    }
});

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();     // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-code').val('');
    $('.input-customer-code').val('');
    $('.input-product-code').val('');
    $('.input-tax-included').val('');
    $('.input-reduced-tax-included').val('');
    $('.input-unit-price').val('');
    $('.input-note').val('');

}

/**
 * 登録更新処理
 */
window.store = function () {
    // モーダル閉じる
    $('#confirm-store').modal('hide');

    // ボタン非活性
    disableButtons();
    // ローディング切替
    changeStoreLoading();

    // submit処理
    $('#editForm').submit();
}

/**
 * 削除処理
 */
window.destory = function () {
    // モーダル閉じる
    $('#confirm-delete').modal('hide');

    // ボタン非活性
    disableButtons();
    // ローディング切替
    changeDeleteLoading();

    // submit処理
    $('#deleteForm').submit();
}

/**
 * ボタン非活性
 */
window.disableButtons = function () {
    // ボタン非活性
    $('#store').prop('disabled', true);
    $('#clear').prop('disabled', true);
    $('#delete').prop('disabled', true);
    $('#return').prop('disabled', true);
}

/**
 * ローディング切替(登録・更新）
 */
window.changeStoreLoading = function () {
    // ローディング切替
    $('#store i').hide();
    $('#store div').show();
}

/**
 * ローディング切替(削除）
 */
window.changeDeleteLoading = function () {
    // ローディング切替
    $('#delete i').hide();
    $('#delete div').show();
}

/**
 * 得意先セレクトボックス変更処理
 */
window.changeCustomer = function () {
    let code = $('.input-customer-select option:selected').data('code');
    // 選択された得意先のコードをテキストボックスに反映
    $('.input-customer-code').val(code);
};

/**
 * 得意先コード入力時の選択肢同期処理
 */
window.inputCustomerCode = function (input) {
    let inputCode = $(input).val();
    let matched = false;

    $('.input-customer-select option').each(function () {
        if ($(this).data('code') == inputCode) {
            $('.input-customer-select').val($(this).val()).trigger('change.select2');
            matched = true;
            return false; // break
        }
    });

    if (!matched) {
        $('.input-customer-select').val('').trigger('change.select2');
    }
};

