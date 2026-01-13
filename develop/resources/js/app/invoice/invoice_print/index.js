/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 「締年月」にフォーカス
    $('#charge_date').focus();
});

/**
 * 全チェック処理(ON/OFF)
 * @param target
 */
window.allCheck = function (target) {
    $('td:first-child input').prop('checked', target.checked);
}

/**
 * 請求書印刷
 */
window.printInvoice = function () {
    // モーダル閉じる
    $('#confirm-print').modal('hide');

    // チェックボックスがONの一覧取得
    let charge_data_ids = $('#target_print:checked').map(function () {
        return $(this).val();
    }).get();

    $('#downloadForm').find('input[name="charge_data_ids"]').val(charge_data_ids);
    $('#downloadForm').find('input[name="issue_date"]').val($('#issue_date').val());

    //帳票出力submit処理
    $('#downloadForm').submit();
}

/**
 * 請求書印刷
 */
window.printInvoicePdf = function () {
    // モーダル閉じる
    $('#confirm-print-pdf').modal('hide');
    // チェックボックスがONの一覧取得
    let charge_data_ids = $('#target_print:checked').map(function () {
        return $(this).val();
    }).get();
    let customer_ids = $('#target_print:checked').map(function () {
        return $(this).val();
    }).get();
    let customer_names = $('#target_print:checked').map(function () {
        return $(this).val();
    }).get();

    $('#showPdfForm').find('input[name="charge_data_ids"]').val(charge_data_ids);
    $('#showPdfForm').find('input[name="customer_ids"]').val(customer_ids);
    $('#showPdfForm').find('input[name="customer_names"]').val(customer_names);
    $('#showPdfForm').find('input[name="issue_date"]').val($('#issue_date').val());

    //帳票出力submit処理
    $('#showPdfForm').submit();
}

/**
 * 請求書発行処理(個別)
 */
window.printInvoicePdfSingle = function (charge_data_id, customer_id, customer_name) {
    let charge_data_ids = charge_data_id;
    let customer_ids = customer_id;
    let customer_names = customer_name;

    $('#showPdfForm').find('input[name="charge_data_ids"]').val(charge_data_ids);
    $('#showPdfForm').find('input[name="customer_ids"]').val(customer_ids);
    $('#showPdfForm').find('input[name="customer_names"]').val(customer_names);
    $('#showPdfForm').find('input[name="issue_date"]').val($('#issue_date').val());

    //帳票出力submit処理
    $('#showPdfForm').submit();
}
