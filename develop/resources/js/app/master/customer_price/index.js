/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 得意先コードにフォーカス
    $('.input-code-start').focus();

    // スクロール位置をセット
    setCookie('master.customer_price');
});
