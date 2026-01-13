/**
 * コード入力の処理
 * @param target
 */
window.inputCode = function (target) {
    if ($(target).val().length > $(target).attr('maxlength')) {
        let txt = $(target).val().slice(0, $(target).attr('maxlength'));
        $(target).val(txt);
    }
}
