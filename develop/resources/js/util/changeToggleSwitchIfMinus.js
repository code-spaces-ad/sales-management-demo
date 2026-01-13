/**
 * マイナス入力トグルのチェックON/OFF
 */
window.changeToggleSwitchIfMinus = function () {
    $('.input-quantity-minus').map(function () {
        let input_quantity = $(this).closest('tr').find('.input-quantity').val();

        if (input_quantity.indexOf('-') !== -1) {
            //マイナス入力チェックON
            $(this).closest('tr').find('.input-quantity-minus').prop('checked', true);
            //クラス削除
            $(this).closest('tr').find('.toggle').removeClass('btn-danger off');
            $(this).closest('tr').find('.input-quantity').removeClass('border border-primary border-3');
            //クラス追加
            $(this).closest('tr').find('.toggle').addClass('btn-primary');
            $(this).closest('tr').find('.input-quantity').addClass('border border-primary border-3');
        }
    });
}
