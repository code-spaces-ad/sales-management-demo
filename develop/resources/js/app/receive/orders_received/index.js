/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 受注日（開始）にフォーカス
    $('#order_date_start').focus();

    // 担当者セレクトボックスチェンジイベント発火
    $('.input-employee-select').change();
    // 得意先セレクトボックスチェンジイベント発火
    $('.input-customer-select').change();

    /**
     * アコーディオンOPEN/CLOSE
     */
    accordionOpenClose();
});
