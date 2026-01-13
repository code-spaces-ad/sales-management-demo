/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // コード（開始）にフォーカス
    $('#code_start').focus();

    // カテゴリチェンジ　イベント発火
    changeCategory();

    // スクロール位置をセット
    setCookie('master.products');
});
