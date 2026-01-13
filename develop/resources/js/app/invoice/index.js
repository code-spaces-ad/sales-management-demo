/* 請求処理画面の共通処理 */

/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 得意先セレクトボックスチェンジイベント発火
    $('.input-customer-select').change();
    // 部門セレクトボックスチェンジイベント発火
    $('.input-department-select').change();
    // 事業所セレクトボックスチェンジイベント発火
    $('.input-office-facility-select').change();
});
