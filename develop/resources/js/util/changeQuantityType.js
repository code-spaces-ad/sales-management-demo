/**
 * 数量マイナス入力
 */
window.changeQuantityType = function (target) {
    let row = $(target).closest('tr');
    let input_quantity = row.find('.input-quantity');
    //数量(10進数)
    let quantity = parseInt(input_quantity.val(), 10);

    //ボーダー関連のクラス削除(bootstrap)
    input_quantity.removeClass('border border-primary border-3');

    //数値か判定
    if (!isFinite(quantity)) {
        input_quantity.val(0);
    }

    //.pc-no-display と同じ画面サイズで比較
    if (window.matchMedia('(min-width:768px)').matches) {
        return true;
    }

    //マイナスを取り除いた数量
    quantity = input_quantity.val().replace(/-/g, '');

    input_quantity.val(quantity);

    //トグルがチェックONか判定
    if (!row.find('.input-quantity-minus').prop('checked')) {
        return true;
    }

    //ボーダー関連のクラス追加(bootstrap)
    input_quantity.addClass('border border-primary border-3');

    //マイナスを取り除いた数量が0か判定
    if (quantity !== '0') {
        //マイナス付き数量セット
        input_quantity.val('-' + quantity);
    }
}
