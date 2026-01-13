/**
 * チェックPOS
 */
window.checkPos = function () {
    let link_pos = $('#link_pos').val() === '1' ? true : false;
    $('[data-target="#confirm-store"]').prop('disabled', link_pos);
    $('[data-target="#confirm-store-showpdf"]').prop('disabled', link_pos);
    $('[data-target="#confirm-delete"]').prop('disabled', link_pos);
    $('[data-target="#confirm-copy"]').prop('disabled', link_pos);
}
