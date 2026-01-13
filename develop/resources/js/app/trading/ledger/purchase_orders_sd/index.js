/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 仕入日（開始）にフォーカス
    $('#order_date_start').focus();

    // 担当者セレクトボックスチェンジイベント発火
    $('.input-employee-select').change();
});
