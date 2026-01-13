/**
 * 得意先検索モーダルが閉じた後のフォーカス移動判定フラグ
 * @type {boolean}
 */
let flgChangeFocusSearchCustomer = false;

/**
 * フォーム変更フラグ
 * @type {boolean}
 */
let flgChangeForm = false;

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
 * フォーム読み込み時に処理
 */
window.onload = function () {
    // 締処理済みチェック
    checkChargeClosed(true);
}

/**
 * ロードイベントに追加
 */
window.addEventListener('load', function () {
    // 合計値セット
    setTotalAmount();

    /**
     * イベント監視開始
     */
    $(window).on('beforeunload', unloadHandler);
    /**
     * イベント監視解除
     */
    $('#editForm').on('submit', function (event) {
        $(window).off('beforeunload', unloadHandler);
    });
    /**
     * フォーム変更イベント
     */
    $('#editForm').on('change', function (event) {
        flgChangeForm = true;
    });

    // 初期表示用
    changeDepartment();
    changeOfficeFacility();

    // 伝票日付にフォーカス
    $('#order_date').focus();
    // 各金額項目、合計の桁区切りセット
    $("[class*='input-amount-'], .input-deposit").each(function () {
        // 桁区切りを一旦解除
        let amount = $(this).val().replace(/,/g, '');
        // 桁区切りを再セット
        $(this).val(parseInt(amount).toLocaleString());
    });

    // 各金額項目フォーカスイベント
    $("[class*='input-amount-']").focus(function () {
        // 桁区切りを一旦解除
        let amount = $(this).val().replace(/,/g, '');
        $(this).val(amount);

        // type変更
        $(this).get(0).type = 'number';

        // 全選択にする
        $(this).select();
    });

    // 各金額項目キーダウンイベント
    $("[class*='input-amount-']").keydown(function (event) {
        // 「type='number'」は「e」が入力可能なので除外
        if (event.key === 'e') {
            return false;
        }

        // 「.」を除外 ※小数を使用しないため。
        if (event.key === '.') {
            return false;
        }
    });

    // 各金額項目ブラーイベント
    $("[class*='input-amount-']").blur(function () {
        let amount = $(this).val().replace(/[^\-0-9]/g, '');   // 数値とマイナスのみ
        if (amount.length === 0) {
            amount = '0';
        }

        // typeを元に戻す
        $(this).get(0).type = 'text';

        // 桁区切りを再設定
        $(this).val(parseInt(amount).toLocaleString());
        // 合計値セット
        setTotalAmount();
    });

    // 得意先セレクトボックスチェンジイベント発火
    $('.input-customer-select').change();

    // 初期化
    //flgChangeForm = {{ $errors->any() ? 'true' : 'false' }};
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;

    /**
     * 得意先検索ボタンフォーカスインイベント
     */
    $('[data-target="#search-customer"]').on('focusin', function () {
        if (flgChangeFocusSearchCustomer) {
            // 得意先リストにフォーカス移動
            $('.input-customer-select').focus();
            // フラグをOFF
            flgChangeFocusSearchCustomer = false;
        }
    });

    /**
     * 得意先コードキーダウンインイベント
     */
    $('.input-customer-code').keydown(function (event) {
        let code = event.code;
        // スペース押下時、かつ未入力の場合
        if (code === 'Space' && $(this).val().length === 0) {
            // 得意先検索ボタンクリック
            $('[data-target="#search-customer"]').click();
            return false;
        }
    });

    // 部門チェンジ　イベント発火
    changeDepartment();
});

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();     // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-order-date').val($('#default_order_date').val());
    $('.input-customer-id').val('');
    $('.input-constr-site-number').val('');
    $('.input-note').val('');

    // 各金額項目ゼロセット
    $("[class*='input-amount-']").val('0');
    // 各備考項目クリア
    $("[class*='input-note-']").val('');
    $('.input-deposit-subtotal').val('0');
    $('.input-adjust-subtotal').val('0');
    $('.input-deposit').val('0');
    // 手形項目クリア
    $('.input-bill-date').val('');
    $('.input-bill-number').val('');

    $('.input-customer-select').prop('selectedIndex', 0);
    changeCustomerCreateEdit();

    // 締処理済みチェック
    checkChargeClosed(true);
}

/**
 * モーダルクローズ
 */
window.modalClose = function (target) {
    $(target).modal('hide');
}

/**
 * 得意先コード変更時の処理
 * @param target
 */
window.changeCustomerCodeCreateEdit = function (target) {

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
        $('.input-customer-select').prop('selectedIndex', 0);
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');

    // 得意先セレクトボックス変更処理
    changeCustomerCreateEdit();
}

/**
 * 得意先セレクトボックス変更処理
 */
window.changeCustomerCreateEdit = function () {
    let code = $('.input-customer-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    let customer_id = $('.input-customer-select option:selected').val();

    // 選択された得意先名のコードをセット
    $('.input-customer-code').val(code);

    // 選択された得意先名の請求残高をセット
    setBillingBalance(customer_id);

    // 締処理済みチェック
    checkChargeClosed(true);
}

/**
 * 合計値セット
 */
window.setTotalAmount = function () {
    let amountCash = $('.input-amount-cash').val().replace(/,/g, '');
    let amountCheck = $('.input-amount-check').val().replace(/,/g, '');
    let amountTransfer = $('.input-amount-transfer').val().replace(/,/g, '');
    let amountBill = $('.input-amount-bill').val().replace(/,/g, '');
    let amountOffset = $('.input-amount-offset').val().replace(/,/g, '');
    let amountDiscount = $('.input-amount-discount').val().replace(/,/g, '');
    let amountFee = $('.input-amount-fee').val().replace(/,/g, '');
    let amountOther = $('.input-amount-other').val().replace(/,/g, '');

    let subTotal = parseInt(amountCash) + parseInt(amountCheck) + parseInt(amountTransfer)
        + parseInt(amountBill) + parseInt(amountOffset);
    let adjustTotal = parseInt(amountDiscount) + parseInt(amountFee) + parseInt(amountOther)
    let totalAmount = subTotal + adjustTotal;

    // 小計セット
    $('.input-deposit-subtotal').val(subTotal.toLocaleString());
    $('.input-adjust-subtotal').val(adjustTotal.toLocaleString());
    // 合計セット
    $('.input-deposit').val(totalAmount.toLocaleString());
    // 入金後残高
    let customerBillingBalance = $('.input-customer-billing-balance').val().replace(/,/g, '');
    if (customerBillingBalance === '-') {
        $('.input-balance-after-deposit').val('0');
    } else {
        $('.input-balance-after-deposit').val((customerBillingBalance - totalAmount).toLocaleString());

        if ((customerBillingBalance - totalAmount) >= 0) {
            // プラス差額
            $('.input-balance-after-deposit').css('color', 'black');
        } else {
            // マイナス差額
            $('.input-balance-after-deposit').css('color', 'red');
        }
    }
}

/**
 * 編集フォームSubmit処理
 * @returns {boolean}
 */
window.editFormSubmit = function () {
    // 各金額項目から桁区切りをはずしておく
    $("[class*='input-amount-']").each(function (index) {
        let amount = $(this).val().replace(/,/g, '');
        $(this).val(amount);
    });

    let deposit = $('.input-deposit').val().replace(/,/g, '');
    $('.input-deposit').val(deposit);
    $('.input-deposit').prop('disabled', false);    // disabled を解除

    return true;
}

/**
 * 伝票日付変更処理
 */
window.changeOrderDate = function () {
    let customer_id = $('.input-customer-select option:selected').val();
    // 選択された得意先名の請求残高をセット
    setBillingBalance(customer_id);
}

/**
 * 請求残高を取得
 */
window.setBillingBalance = function (customer_id) {

    // 実施前にクリア
    $('.input-label-closing-info').text('');
    $('.input-balance-after-deposit').val('0');
    $('.input-customer-billing-balance').val('-');
    $('.input-balance-after-deposit').css('color', 'black');

    if (customer_id === '') {
        $('.input-customer-billing-balance').val('-');
        return;
    }

    let url = $('.hidden-api-get-billing-balance-url').val();
    let order_date = $('.input-order-date').val();

    // 指定の得意先の請求残高を取得(API非同期通信)
    axios.get(url, {
        params: {
            customer_id: customer_id,
            order_date: order_date,
        }
    }).then(response => {
        // 正常処理時
        $('.input-customer-billing-balance').val((response.data['charge_total']).toLocaleString());
        if (response.data['close_info'] !== '') {
            $('.input-label-closing-info').text('(' + response.data['close_info'] + ')');
        }
        // 合計
        setTotalAmount();
    }).catch(error => {
        // エラー時
        console.error(error);
    });
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
 * 複製処理
 */
window.copy = function () {
    // モーダル閉じる
    $('#confirm-copy').modal('hide');

    // ボタン非活性
    disableButtons();
    // ローディング切替
    changeCopyLoading();

    // submit処理
    $('#copyForm').submit();
}

