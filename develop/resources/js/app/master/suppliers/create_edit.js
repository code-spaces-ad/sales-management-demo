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

    // 開始買掛残高　桁区切りを一旦解除
    let start_account_receivable_balance = $('.input-start-account-receivable-balance').val().replace(/,/g, '');
    $('.input-start-account-receivable-balance').val(start_account_receivable_balance);
    // 開始買掛残高　桁区切りを再セット
    $('.input-start-account-receivable-balance').val(parseInt(start_account_receivable_balance).toLocaleString());

    // 開始買掛残高 フォーカスイベント
    $(".input-start-account-receivable-balance").focus(function () {
        // 桁区切りを一旦解除
        let start_account_receivable_balance = $(this).val().replace(/,/g, '');
        $(this).val(start_account_receivable_balance);
        // type変更
        $(this).get(0).type = 'number';
        // 全選択にする
        $(this).select();
    });

    // 開始買掛残高 キーダウンイベント
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

    // 開始買掛残高 ブラーイベント
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

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;
    flgEditRoute = $('.hidden-is-edit-route').val() ? true: false;

    if (!flgChangeForm && !flgEditRoute){
        searchAvailableNumber('suppliers');
        //searchAvailableSortNumber();
    }

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

    $('.input-postal-code1').val('');
    $('.input-postal-code2').val('');
    $('.input-address1').val('');
    $('.input-address2').val('');
    $('.input-tel-number').val('');
    $('.input-fax-number').val('');
    $('.input-email').val('');
    $('.input-start-account-receivable-balance').val(0);
    $('.input-note').val('');
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

