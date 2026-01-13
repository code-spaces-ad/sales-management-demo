/**
 * 請求先（得意先）検索モーダルが閉じた後のフォーカス移動判定フラグ
 * @type {boolean}
 */
let flgChangeFocusSearchCustomer = false;
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

    // 開始売掛残高　桁区切りを一旦解除
    let start_account_receivable_balance = $('.input-start-account-receivable-balance').val().replace(/,/g, '');
    $('.input-start-account-receivable-balance').val(start_account_receivable_balance);
    // 開始売掛残高　桁区切りを再セット
    $('.input-start-account-receivable-balance').val(parseInt(start_account_receivable_balance).toLocaleString());

    // 開始売掛残高 フォーカスイベント
    $(".input-start-account-receivable-balance").focus(function () {
        // 桁区切りを一旦解除
        let start_account_receivable_balance = $(this).val().replace(/,/g, '');
        $(this).val(start_account_receivable_balance);
        // type変更
        $(this).get(0).type = 'number';
        // 全選択にする
        $(this).select();
    });

    // 開始売掛残高 キーダウンイベント
    $(".input-start-account-receivable-balance").keydown(function (event) {
        // 「type='number'」は「e」が入力可能なので除外
        if (event.key === 'e') {
            return false;
        }

        // 「.」を除外 ※小数を使用しないため。
        if (event.key === '.') {
            return false;
        }
    });

    // 開始売掛残高 ブラーイベント
    $(".input-start-account-receivable-balance").blur(function () {
        let amount = $(this).val().replace(/[^\-0-9]/g, '');   // 数値とマイナスのみ
        if (amount.length === 0) {
            amount = '0';
        }
        // typeを元に戻す
        $(this).get(0).type = 'text';
        // 桁区切りを再設定
        $(this).val(parseInt(amount).toLocaleString());
    });

    // 請求先（得意先）セレクトボックスチェンジイベント発火
    $('.input-billing-customer-select').change();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;
    flgEditRoute = $('.hidden-is-edit-route').val() ? true: false;

    if (!flgChangeForm && !flgEditRoute){
        searchAvailableNumber('customers');
        searchAvailableSortNumber('customers');
    }

    /**
     * 請求先（得意先）検索ボタンフォーカスインイベント
     */
    $('[data-target="#search-billing-customer"]').on('focusin', function () {
        if (flgChangeFocusSearchCustomer) {
            // 得意先リストにフォーカス移動
            $('.input-billing-customer-select').focus();
            // フラグをOFF
            flgChangeFocusSearchCustomer = false;
        }
    });

    /**
     * 請求先コードキーダウンインイベント
     */
    $('.input-billing-customer-code').keydown(function (event) {
        let code = event.code;
        // スペース押下時、かつ未入力の場合
        if (code === 'Space' && $(this).val().length === 0) {
            // 請求先検索ボタンクリック
            $('[data-target="#search-billing-customer"]').click();
            return false;
        }
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
    $('.input-sort-code').val('');
    $('.input-name').val('');
    $('.input-name-kana').val('');

    $('.input-postal-code1').val('');
    $('.input-postal-code2').val('');
    $('.input-address1').val('');
    $('.input-address2').val('');
    $('.input-tel-number').val('');
    $('.input-fax-number').val('');
    $('.input-email').val('');
    $('.input-start-account-receivable-balance').val(0);
    $('.input-note').val('');

    $('.input-billing-customer-select').val(0);
    changeBillingCustomer();

    $('.input-closing-date').val(0);
}

/**
 * 請求先（得意先）コード変更時の処理
 * @param target
 */
window.changeBillingCustomerCode = function(target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-billing-customer-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-billing-customer-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 請求先（得意先）セレクトボックス変更処理
 */
window.changeBillingCustomer = function () {
    let code = $('.input-billing-customer-select option:selected').data('code');
    // 選択された請求先名（得意先名）のコードをセット
    $('.input-billing-customer-code').val(code);
}

/**
 * 請求先（得意先）検索モーダル用 得意先選択
 * @param target
 */
window.selectBillingCustomerSearchCustomerModal = function(target) {
    let targetValue = $(target).data('code');

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    $('.input-billing-customer-code').val(targetValue);

    let targetOption = ".input-billing-customer-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-billing-customer-select').prop('selectedIndex', 0).change();
    }

    // 請求先（得意先）検索モーダルを閉じる
    $('#search-billing-customer').modal('hide');

    // フォーカス移動ON
    flgChangeFocusSearchCustomer = true;
}

/**
 * 編集フォームSubmit処理
 * @returns {boolean}
 */
window.editFormSubmit = function () {
    let start_account_receivable_balance = $('.input-start-account-receivable-balance').val().replace(/,/g, '');
    $('.input-start-account-receivable-balance').val(start_account_receivable_balance);

    return true;
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
 * 住所検索
 */
window.searchAddress = function () {
    // ローディング&非活性
    $('#address-spinner i').hide();
    $('#address-spinner div').show();
    $('#address-spinner').prop('disabled', true);

    let postal_code = $('#postal_code1').val() + $('#postal_code2').val();

    axios.post(`/api/search_address/`, {
        'postal_code': postal_code
    })
        .then(response => {
            let address = response.data[0];
            if (!address) {
                alert("住所が取得できませんでした");
            } else {
                $('.input-address1').val(address);
            }
        })
        .catch(error => {
            alert("住所が取得できませんでした");
            console.error(error);
        })
        .finally(() => {
            // ローディング&非活性　解除
            $('#address-spinner i').show();
            $('#address-spinner div').hide();
            $('#address-spinner').prop('disabled', false);
        });
}

