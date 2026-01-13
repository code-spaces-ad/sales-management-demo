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
 * ロードイベントに追加
 */
window.addEventListener('load', function () {

    let editForm = '#editForm';

    /**
     * イベント監視開始
     */
    $(window).on('beforeunload', unloadHandler);

    /**
     * イベント監視解除
     */
    $(editForm).on('submit', function () {
        $(window).off('beforeunload', unloadHandler);
    });

    /**
     * フォーム変更イベント
     */
    $(editForm).on('change', function () {
        flgChangeForm = true;
    });

    /**
     * タブOPEN/CLOSE
     */
    $(function () {
        $(".select-tab").on("click", function () {
            // タブ内の全ての要素を非表示
            $(this).parent().parent().find('.setting-item').hide();

            // タブをクリックした時のデザイン調整 (クリックしたタブの背景を白、それ以外をグレー)
            $(this).parent().find('.select-tab').addClass('active');
            $(this).parent().find('.select-tab').removeClass('disabled');
            $(this).addClass('disabled');
            $(this).removeClass('active');

            // クリックしたタブと同じIDの要素を表示
            $(this).parent().parent().find('.' + $(this).attr('id')).show();
        });
    });

});
