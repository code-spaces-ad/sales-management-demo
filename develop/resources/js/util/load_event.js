/**
 * ロードイベントに追加
 */
window.addEventListener('ready', function () {
    //select2 Focus
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });
});
