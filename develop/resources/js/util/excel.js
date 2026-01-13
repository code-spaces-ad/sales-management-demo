/**
 * Excel出力処理
 */
window.downloadReportExcel = function (url) {
    'use strict';

    $('.invalid-feedback').remove();                // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア
    $('.alert').remove();

    let downloadForm = $("#download_form");
    downloadForm.find('input[type="hidden"]').remove();

    $("#searchForm").serialize().split('&').forEach(item => {
        let [name, value] = item.split('=');
        name = decodeURIComponent(name);
        value = decodeURIComponent(value);

        $('#download_form').append(
            $('<input>', { type: 'hidden', name: name, value: value })
        );
    });

    $("#download_form").attr('action', url);
    $("#download_form").submit();
};
