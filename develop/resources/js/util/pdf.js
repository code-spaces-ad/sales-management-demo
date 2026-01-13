/**
 * PDF出力処理
 */
window.downloadReportPdf = function (url) {
    $('.invalid-feedback').remove();                // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア
    $('.alert').remove();

    startPreloader();

    let datalist = {};
    $("#searchForm").serialize().split('&').forEach(item => {
        let [name, value] = item.split('=');
        name = decodeURIComponent(name);
        value = decodeURIComponent(value);
        datalist[name] = value;
    });

    downloadPdfAjax(url, datalist);
};

/**
 * PDF出力処理
 */
window.downloadPdfAjax = function (url, datalist) {
    $.ajax({
        url: url,
        type: "GET",
        data: datalist,
        cache: false,
    }).done(function(data, textStatus, jqXHR) {
        console.log(data);
        if (data.indexOf('.pdf') != -1) {
            window.open(data, '_blank');
        } else {
            $('#loading').before(getPdfErrorMessage(data));
        }
        stopPreloader();
    }).fail(function(jqXHR, textStatus, errorThrown) {
        stopPreloader();
        console.log('Error!!! : ' + jqXHR.status + ' : ' + textStatus);
    });
};

/**
 * エラーメッセージ取得
 *
 * @param msg
 * @returns {string}
 */
function getPdfErrorMessage(msg) {
    return'<div class="alert alert-dismissible fade show alert-danger" role="alert">' +
            '<i class="fas fa-exclamation-circle"></i>' +
            msg +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">×</span>' +
            '</button>' +
        '</div>';
}
