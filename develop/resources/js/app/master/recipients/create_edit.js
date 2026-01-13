
/**
 * 得意先検索モーダルが閉じた後のフォーカス移動判定フラグ
 * @type {boolean}
 */
let flgChangeFocusSearchBranch = false;
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

    // 支所セレクトボックスチェンジイベント発火
    $('.input-branch-select').change();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;
    flgEditRoute = $('.hidden-is-edit-route').val() ? true: false;

    if (!flgChangeForm && !flgEditRoute){
        //searchAvailableNumber();
    }

    /**
     * 支所検索ボタンフォーカスインイベント
     */
    $('[data-target="#search-branch"]').on('focusin', function () {
        if (flgChangeFocusSearchBranch) {
            // 支所名リストにフォーカス移動
            $('.input-branch-select').focus();
            // フラグをOFF
            flgChangeFocusSearchBranch = false;
        }
    });

    /**
     * 得意先コードキーダウンインイベント
     */
    $('.input-branch-code').keydown(function (event) {
        let code = event.code;
        // スペース押下時、かつ未入力の場合
        if (code === 'Space' && $(this).val().length === 0) {
            // 支所名検索ボタンクリック
            $('[data-target="#search-branch"]').click();
            return false;
        }
    });

    $.fn.autoKana('input[name="recipient_name"] ', 'input[name="name_kana"]', {katakana: false});

});


/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();     // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-recipient-name').val('');

    $('.input-branch-code').val('');

    $('.input-branch-select').val(0);
    changeBranch();
}

/**
 * 得意先セレクトボックス変更処理
 */
window.changeBranch = function () {
    let code = $('.input-branch-select option:selected').data('code');
    // 選択された支所名（支所名）のコードをセット
    $('.input-branch-code').val(code);
}

/**
 * 支所検索モーダル用 得意先選択
 * @param target
 */
window.selectBranchSearchBranchModal = function(target) {
    let targetValue = $(target).data('branch-id');

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    $('.input-branch-id').val(targetValue);

    let targetOption = ".input-branch-select option[value='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-branch-select').prop('selectedIndex', 0).change();
    }

    // 支所検索モーダルを閉じる
    $('#search-branch').modal('hide');

    // フォーカス移動ON
    flgChangeFocusSearchBranch = true;
}
