/* 仕入処理画面の共通処理 */

/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 仕入先セレクトボックスチェンジイベント発火
    $('.input-supplier-select').change();

    // 部門セレクトボックスチェンジイベント発火
    $('.input-department-select').change();

    // 事業所セレクトボックスチェンジイベント発火
    $('.input-office-facility-select').change();

    /**
     * アコーディオンOPEN/CLOSE
     */
    accordionOpenClose();
});
