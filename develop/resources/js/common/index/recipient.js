/**
 * 納品先セレクトボックスフィルタリング
 */
window.filterRecipient = function () {
    // 全体を一旦クリア
    $(".hidden-recipient").unwrap();
    $(".input-recipient-select option").removeClass('hidden-recipient');

    let targetCustomerId = $('.input-customer-select option:selected').val();
    let targetBranchId = $('.input-branch-select option:selected').val();
    if (targetCustomerId !== '' || targetBranchId !== '') {
        $(".input-recipient-select option").each(function () {
            if ($(this).data('branch-id') === undefined) {
                return true;
            }

            if (targetCustomerId !== '' && $(this).data('customer-id') !== parseInt(targetCustomerId)) {
                // 支所IDが不一致は非表示セット
                $(this).addClass('hidden-recipient');
                $(this).wrap("<span class='d-none'></span>");
                return true;
            }

            if (targetBranchId !== '' && $(this).data('branch-id') !== parseInt(targetBranchId)) {
                // 支所IDが不一致は非表示セット
                $(this).addClass('hidden-recipient');
                $(this).wrap("<span class='d-none'></span>");
            }
        });
    }

    let selectIndex = $('.input-recipient-select').prop('selectedIndex');
    // 選択状態でない、または選択状態が非表示の場合
    if (selectIndex === -1 || $('.input-recipient-select :selected').hasClass('hidden-recipient')) {
        // 未選択状態に変更する
        $(".input-recipient-select option:selected").prop("selected", false);
        $('.input-recipient-select').prop('selectedIndex', 0).change();
    }
}
