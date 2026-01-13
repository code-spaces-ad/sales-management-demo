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

    // 得意先セレクトボックスチェンジイベント発火
    $('.input-customer-select').change();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;
    flgEditRoute = $('.hidden-is-edit-route').val() ? true: false;

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
    $.fn.autoKana('input[name="branch_name"] ', 'input[name="name_kana"]', {katakana: false});
});

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();     // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-branch-name').val('');

    $('.input-mnemonic_name').val('');

    $('.input-customer-code').val('');

    $('.input-customer-select').val(0);
    changeCustomer();
}
