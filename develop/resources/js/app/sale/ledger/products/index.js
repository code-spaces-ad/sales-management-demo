/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 「商品コード」にフォーカス
    $('.input-product-code').focus();

    // 商品セレクトボックスチェンジイベント発火
    $('.input-product-select').change();
});
