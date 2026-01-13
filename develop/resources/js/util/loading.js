/**
 * Custom jQuery functions
 */
jQuery.fn.extend({
    removeError: function () {
        removeError(this);
        return this;
    },
    setSpinner: function () {
        setSpinner(this);
        return this;
    },
    removeSpinner: function () {
        removeSpinner(this);
        return this;
    },
});

/**
 * Start Preloader
 */
window.startPreloader = () => {
    $('#loading').fadeIn();
}

/**
 * Stop Preloader
 */
window.stopPreloader = () => {
    $('#loading').fadeOut();
}

/**
 * Create a Spinner
 *
 * @param target
 */
window.setSpinner = (target) => {
    if ($(target).find('i.fa-spinner').length > 0) {
        return false;
    }
    $(target).removeError().prop('disabled', true).append(`<i class="fas fa-spinner fa-spin ml-1"></i>`);
    startPreloader();
}

/**
 * Remove a Spinner
 *
 * @param target
 */
window.removeSpinner = (target) => {
    $(target).prop('disabled', false).find('i.fa-spinner').remove();
    stopPreloader();
}

/**
 * Remove error message from a target
 *
 * @param target
 */
window.removeError = (target) => {
    $(target).removeClass('is-invalid');
    $(target).parent().find('div.invalid-feedback').remove();
}

/**
 * ボタン非活性
 */
window.disableButtons = function () {
    // ボタン非活性
    $('#store').prop('disabled', true);
    $('#show_pdf').prop('disabled', true);
    $('#clear').prop('disabled', true);
    $('#copy').prop('disabled', true);
    $('#complete').prop('disabled', true);
    $('#delete').prop('disabled', true);
    $('#return').prop('disabled', true);
    $('#reject').prop('disabled', true);
    $('#update').prop('disabled', true);
}

/**
 * ローディング切替(登録・更新）
 */
window.changeStoreLoading = function () {
    // ローディング切替
    $('#store i').hide();
    $('#store div').show();
}

/**
 * ローディング切替(削除）
 */
window.changeDeleteLoading = function () {
    // ローディング切替
    $('#delete i').hide();
    $('#delete div').show();
}

/**
 * ローディング切替(複製）
 */
window.changeCopyLoading = function () {
    // ローディング切替
    $('#copy i').hide();
    $('#copy div').show();
}




