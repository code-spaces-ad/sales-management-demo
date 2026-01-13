/**
 * 仕入先検索モーダルが閉じた後のフォーカス移動判定フラグ
 * @type {boolean}
 */
let flgChangeFocusSearchSupplier = false;

/**
 * フォーム変更フラグ
 * @type {boolean}
 */
let flgChangeForm = false;

/**
 * 画面遷移アラート
 *
 * @param event
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
    checkPurchaseClosed(true);
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

    // 伝票日付にフォーカス
    $('#order_date').focus();
    // 各金額項目、合計の桁区切りセット
    $("[class*='input-amount-'], .input-payment").each(function () {
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

    // 仕入先セレクトボックスチェンジイベント発火
    $('.input-supplier-select').change();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;

    /**
     * 仕入先検索ボタンフォーカスインイベント
     */
    $('[data-target="#search-supplier"]').on('focusin', function () {
        if (flgChangeFocusSearchSupplier) {
            // 仕入先リストにフォーカス移動
            $('.input-supplier-select').focus();
            // フラグをOFF
            flgChangeFocusSearchSupplier = false;
        }
    });

    /**
     * 仕入先コードキーダウンインイベント
     */
    $('.input-supplier-code').keydown(function (event) {
        let code = event.code;
        // スペース押下時、かつ未入力の場合
        if (code === 'Space' && $(this).val().length === 0) {
            // 仕入先検索ボタンクリック
            $('[data-target="#search-supplier"]').click();
            return false;
        }
    });

    // 初期表示用
    changeDepartment();
    changeOfficeFacility();
});

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();     // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-order-date').val($('#default_order_date').val());
    $('.input-supplier-id').val('');
    $('.input-constr-site-number').val('');
    $('.input-note').val('');

    // 各金額項目ゼロセット
    $("[class*='input-amount-']").val('0');
    // 各備考項目クリア
    $("[class*='input-note-']").val('');
    $('.input-payment-subtotal').val('0');
    $('.input-adjust-subtotal').val('0');
    $('.input-payment').val('0');
    // 手形項目クリア
    $('.input-bill-date').val('');
    $('.input-bill-number').val('');

    $('.input-supplier-select').prop('selectedIndex', 0).change();
    changeSupplierCreateEdit();

    // 締処理済みチェック
    checkPurchaseClosed(true);
}

/**
 * モーダルクローズ
 */
window.modalClose = function(target) {
    $(target).modal('hide');
}

/**
 * 仕入先コード変更時の処理
 * @param target
 */
window.changeSupplierCodeCreateEdit = function(target) {
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
    let supplier_id = $('.input-supplier-select option:selected').val();
    let payment_balance = $('.input-supplier-select option:selected').data('payment-balance') ?? 0;

    // 選択された仕入先名のコードをセット
    $('.input-supplier-code').val(code);
    // 選択された仕入先名の支払残高をセット
    $('.input-supplier-payment-balance').val(payment_balance.toLocaleString());

    // 選択された仕入先名の支払残高をセット
    setPaymentBalance(supplier_id);

    // 締処理済みチェック
    checkPurchaseClosed(true);
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
    $('.input-payment-subtotal').val(subTotal.toLocaleString());
    $('.input-adjust-subtotal').val(adjustTotal.toLocaleString());
    // 合計セット
    $('.input-payment').val(totalAmount.toLocaleString());
    // 支払後残高
    let supplierPaymentBalance = $('.input-supplier-payment-balance').val().replace(/,/g, '');
    if (supplierPaymentBalance === '-') {
        $('.input-balance-after-payment').val('0');
    } else {
        $('.input-balance-after-payment').val((supplierPaymentBalance - totalAmount).toLocaleString());

        if ((supplierPaymentBalance - totalAmount) >= 0) {
            // プラス差額
            $('.input-balance-after-payment').css('color', 'black');
        } else {
            // マイナス差額
            $('.input-balance-after-payment').css('color', 'red');
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

    let payment = $('.input-payment').val().replace(/,/g, '');
    $('.input-payment').val(payment);
    $('.input-payment').prop('disabled', false);    // disabled を解除

    return true;
}

/**
 * 伝票日付変更処理
 */
window.changeOrderDate = function () {
    let supplier_id = $('.input-supplier-select option:selected').val();
    // 選択された仕入先名の支払残高をセット
    setPaymentBalance(supplier_id);
}

/**
 * 支払残高を取得
 */
window.setPaymentBalance = function(supplier_id) {

    // 実施前にクリア
    $('.input-label-closing-info').text('');
    $('.input-balance-after-deposit').val('0');
    $('.input-supplier-payment-balance').val('-');
    $('.input-balance-after-deposit').css('color', 'black');

    if (supplier_id === '') {
        $('.input-supplier-payment-balance').val('-');
        return;
    }

    let url = $('.hidden-api-suppliers-get-payment-balance-url').val();
    let order_date = $('.input-order-date').val();

    // 指定の得意先の請求残高を取得(API非同期通信)
    axios.get(url, {
        params: {
            supplier_id: supplier_id,
            order_date: order_date,
        }
    }).then(response => {
        // 正常処理時
        $('.input-supplier-payment-balance').val((response.data['purchase_total']).toLocaleString());
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
