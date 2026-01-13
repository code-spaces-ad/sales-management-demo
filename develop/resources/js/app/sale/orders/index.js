/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    $('#order_number').focus();
});

function changeBranch() {
    // 納品先セレクトボックスフィルタリング
    filterRecipient();
}
