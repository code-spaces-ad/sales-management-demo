/* マスター管理画面の共通処理 */

/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 得意先セレクトボックスチェンジイベント発火
    $('.input-customer-select').change();

    /**
     * アコーディオンOPEN/CLOSE
     */
    accordionOpenClose();
});
