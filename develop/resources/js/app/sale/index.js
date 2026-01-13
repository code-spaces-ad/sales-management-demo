/* 売上管理画面の共通処理 */

/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 得意先セレクトボックスチェンジイベント発火
    $('.input-customer-select').change();

    // 得意先セレクトボックスチェンジイベント発火(from～to)
    $('.input-customer-start-select').change();
    $('.input-customer-end-select').change();

    // 商品セレクトボックスチェンジイベント発火(from～to)
    $('.input-product-start-select').change();
    $('.input-product-end-select').change();

    // 担当者セレクトボックスチェンジイベント発火
    $('.input-employee-select').change();

    // 部門セレクトボックスチェンジイベント発火
    $('.input-department-select').change();

    // 事業所セレクトボックスチェンジイベント発火
    $('.input-office-facility-select').change();

    /**
     * アコーディオンOPEN/CLOSE
     */
    accordionOpenClose();
});
