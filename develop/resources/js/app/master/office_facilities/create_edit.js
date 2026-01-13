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

    /**
     * 部門変更イベント
     */
    $('.input-department-select').on('change', function () {
        changeDepartmentMaster();
    });

    // コードにフォーカス
    $('.input-code').focus();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;
    flgEditRoute = $('.hidden-is-edit-route').val() ? true: false;

    if (!flgChangeForm && !flgEditRoute){
        searchAvailableNumber('office_facilities', 'department_id', $('.input-department-select').val());
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
    $('.input-department-select').prop('selectedIndex', 0).change();
    $('.input-employee-select').prop('selectedIndex', 0).change();
    $('.input-note').val('');
}

/**
 * 部門セレクトボックス変更処理
 */
window.changeDepartmentMaster = function () {
    $('.input-code').val('1');
    searchAvailableNumber('office_facilities', 'department_id', $('.input-department-select').val());
}
