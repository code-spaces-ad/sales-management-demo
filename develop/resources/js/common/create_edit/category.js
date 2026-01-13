/**
 * カテゴリセレクトボックス変更処理
 */
window.changeCategory = function () {
    let code = $('.input-category-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 4)
    }
    $('.input-sub-category-select').prop('disabled', false);
    if (!code) {
        $(".input-sub-category-select option:selected").prop("selected", false);
        $('.input-sub-category-select').prop("selectedIndex", 0).change();
        $('.input-sub-category-select').prop('disabled', true);
    }

    // サブカテゴリセレクトボックスフィルタリング
    filterSubCategory();
}
