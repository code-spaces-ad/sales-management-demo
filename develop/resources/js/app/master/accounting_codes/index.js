/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // コード（開始）にフォーカス
    $('#code_start').focus();

    // スクロール位置をセット
    setCookie('master.accounting_codes');
});
