/**
 * 支所セレクトボックス変更処理
 */
window.changeBranch = function () {
    // 納品先セレクトボックスフィルタリング
    filterRecipient();
}

/**
 * 支所セレクトボックスフィルタリング処理
 */
window.filterBranch = function () {
    // 全体を一旦クリア
    $(".hidden-branch").unwrap();
    $(".input-branch-select option").removeClass('hidden-branch');

    let targetCustomerId = $('.input-customer-select option:selected').val();
    if (targetCustomerId !== '') {
        $(".input-branch-select option").each(function () {
            if ($(this).data('customer-id') === undefined) {
                return true;
            }
            if ($(this).data('customer-id') !== parseInt(targetCustomerId)) {
                // 得意先IDが不一致は非表示セット
                $(this).addClass('hidden-branch');
                $(this).wrap("<span class='d-none'></span>");
            }
        });
    }

    let selectIndex = $('.input-branch-select').prop('selectedIndex');
    // 選択状態でない、または選択状態が非表示の場合
    if (selectIndex === -1 || $('.input-branch-select :selected').hasClass('hidden-branch')) {
        // 未選択状態に変更する
        $(".input-branch-select option:selected").prop("selected", false);
        $('.input-branch-select').prop("selectedIndex", 0).change();

        $(".input-recipient-select option:selected").prop("selected", false);
        $('.input-recipient-select').prop('selectedIndex', 0).change();
    }
    filterRecipient();
}
