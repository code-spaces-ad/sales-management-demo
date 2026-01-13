/**
 * サブカテゴリセレクトボックス変更処理
 */
window.changeSubCategory = function () {
}

/**
 * サブカテゴリセレクトボックスフィルタリング処理
 */
window.filterSubCategory = function () {
    // 全体を一旦クリア
    $(".hidden-sub-category").unwrap();
    $(".input-sub-category-select option").removeClass('hidden-sub-category');

    let targetCategoryId = $('.input-category-select option:selected').val();
    if (targetCategoryId !== '') {
        $(".input-sub-category-select option").each(function () {
            if ($(this).data('category-id') === undefined) {
                return true;
            }
            if ($(this).data('category-id') !== parseInt(targetCategoryId)) {
                // IDが不一致は非表示セット
                $(this).addClass('hidden-sub-category');
                $(this).wrap("<span class='d-none'></span>");
            }
        });
    }

    let selectIndex = $('.input-sub-category-select').prop('selectedIndex');
    let filterFirstIndex = $(".input-sub-category-select option:not(.hidden-sub-category)").first().index();
    // 選択状態でない、または選択状態が非表示の場合
    if (selectIndex === -1 || $('.input-sub-category-select :selected').hasClass('hidden-sub-category')) {
        // 未選択状態に変更する
        $(".input-sub-category-select option:selected").prop("selected", false);
        $('.input-sub-category-select').prop("selectedIndex", filterFirstIndex).change();
    }
}
