/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 伝票番号にフォーカス
    $('.input-order-number').focus();

    // 仕入先セレクトボックスチェンジイベント発火
    $('.input-supplier-select').change();
});
