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

    // 会社名にフォーカス
    $('.input-company-name').focus();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;

    // 社印画像チェンジイベント
    $('.input-company-seal-image').change(function () {
        // ファイル名に書き換え
        $('.label-company-seal-image').text($(this).val().replace("C:\\fakepath\\", ""));
    });

    // 社印画像削除イベント
    $('#company_seal_image_del_btn').on('click', function () {
        $('.img-thumbnail-container').hide();
        $('.img-placeholder').show();
        $('.img-file-name').hide();
        $('.img-file-name-placeholder').show();
        $('input[name="company_seal_image_del_flag"]').val(1);
        $(this).hide();
    });
});

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.input-company_name').val('');
    $('.input-representative_name').val('');
    $('.input-postal-code1').val('');
    $('.input-postal-code2').val('');
    $('.input-address1').val('');
    $('.input-address2').val('');
    $('.input-tel-number').val('');
    $('.input-fax-number').val('');
    $('.invoice_number').val('');
}
