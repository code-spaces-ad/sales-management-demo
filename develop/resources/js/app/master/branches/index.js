/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 得意先コードにフォーカス
    $('.input-customer-code').focus();

    // スクロール位置をセット
    setCookie('master.branches');
});
