/**
 * アコーディオンOPEN/CLOSE
 */
window.accordionOpenClose = function () {
    /**
     * アコーディオンOPEN/CLOSE
     */
    $('.open').on('click', function () {
        $(this).closest('tr').next('tr').show();
        $(this).hide();
        $(this).next().show();
    });
    $('.close').on('click', function () {
        $(this).closest('tr').next('tr').hide();
        $(this).hide();
        $(this).prev().show();
    });

    /**
     * アコーディオンOPEN/CLOSE
     */
    $('.centralOpen').on('click', function () {
        $('.detail').show();
        $('.open').hide();
        $('.close').show();
        $(this).hide();
        $(this).next().show();
    });
    $('.centralClose').on('click', function () {
        $('.detail').hide();
        $('.open').show();
        $('.close').hide();
        $(this).hide();
        $(this).prev().show();
    });
}

/**
 * アコーディオンCLOSE/OPEN
 */
window.accordionCloseOpen = function () {
    /**
     * アコーディオンOPEN/CLOSE
     */
    $('.open').on('click', function () {
        $(this).closest('tr').next('tr').show();
        $(this).hide();
        $(this).prev().show();
    });
    $('.close').on('click', function () {
        $(this).closest('tr').next('tr').hide();
        $(this).hide();
        $(this).next().show();
    });

    /**
     * アコーディオンOPEN/CLOSE
     */
    $('.centralOpen').on('click', function () {
        $('.detail').show();
        $('.open').hide();
        $('.close').show();
        $(this).hide();
        $(this).prev().show();
    });
    $('.centralClose').on('click', function () {
        $('.detail').hide();
        $('.open').show();
        $('.close').hide();
        $(this).hide();
        $(this).next().show();
    });
}
