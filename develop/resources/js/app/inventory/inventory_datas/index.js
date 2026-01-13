/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 入出庫日（開始）にフォーカス
    $('#inout_date_start').focus();

    /**
     * アコーディオンOPEN/CLOSE
     */
    accordionCloseOpen();
});
