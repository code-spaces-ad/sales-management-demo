/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 「締年月」にフォーカス
    $('#purchase_date').focus();
});

/**
 * 全チェック処理(ON/OFF)
 * @param target
 */
window.allCheck = function (target) {
    $('td:first-child input').prop('checked', target.checked);
}

/**
 * 仕入締処理(一括)
 *
 * @param route
 */
window.chargeClosingStore = function (route) {
    // モーダル閉じる
    $('#confirm-store').modal('hide');

    // チェックボックスがONの一覧取得
    let supplier_ids = $('.input-target-charge-closing:checked').map(function () {
        if ($(this).closest('tr').children('td')[1].innerText.trim() !== '') {
            return;
        }
        return $(this).val();
    }).get();

    if (supplier_ids.length === 0) {
        alert('仕入締の対象が選択されていません。');
        return;
    }

    // ボタン非活性
    disableClosingButtons();
    // ローディング切替
    changeClosingLoading();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: route,
        method: 'POST',
        data: {
            supplier_ids: supplier_ids,
            searchForm: Object.fromEntries($('#searchForm').serializeArray().map(item => [item.name, item.value])),
        },
    }).done(function (response) {
        // 更新されたcsrfトークンをセット
        $('meta[name="csrf-token"]').attr('content', response['token']);
    }).fail(function (xhr) {
        console.log(xhr);
    });
}

/**
 * 仕入締解除処理(一括)
 */
window.chargeClosingCancel = function () {

    // モーダル閉じる
    $('#confirm-cancel').modal('hide');

    // チェックボックスがONの一覧取得
    let purchase_data_ids = $('.input-target-charge-closing:checked').map(function () {
        if ($(this).closest('tr').children('td')[1].innerText.trim() === '') {
            return;
        }
        return $(this).closest('tr').children('td')[1].innerText.trim();
    }).get();

    if (purchase_data_ids.length === 0) {
        alert('仕入締解除の対象が選択されていません。');
        return;
    }

    // ボタン非活性
    disableClosingButtons();
    // ローディング切替
    changeCancelLoading();

    // submit用のhiddenセット
    $('#cancelForm').find('input[name="purchase_data_ids"]').val(purchase_data_ids);

    // 締処理解除submit処理
    $('#cancelForm').submit();
}

/**
 * 仕入締処理(個別)
 */
window.chargeClosingStoreSingle = function (supplier_id, target_name) {
    console.log(supplier_id);
    if (!confirm('「' + target_name + '」を個別に仕入締処理します\r\nよろしいですか。\r\n※対象伝票が無い場合は処理されません。')) {
        return;
    }

    // ボタン非活性
    disableClosingButtons();

    // submit用のhiddenセット
    $('#searchForm').append(
        $('<input>')
            .attr('type', 'hidden')
            .attr('name', 'supplier_ids')
            .val([supplier_id])
    );
    $('#searchForm').attr('action', $('input[name="purchase_closing_store"]').val());
    $('#searchForm').attr('method', 'POST');

    // 締処理submit処理
    $('#searchForm').submit();
}

/**
 * 仕入締解除処理(個別)
 */
window.chargeClosingCancelSingle = function (purchase_data_id, target_name) {

    let target = $(this);

    if (!confirm('「' + target_name + '」の仕入締を個別に解除します\r\nよろしいですか。')) {
        return;
    }

    // ボタン非活性
    disableClosingButtons();

    // 選択行の仕入データIDを取得
    let purchase_data_ids = [purchase_data_id];

    // submit用のhiddenセット
    $('#cancelForm').find('input[name="purchase_data_ids"]').val(purchase_data_ids);

    // 締処理解除submit処理
    $('#cancelForm').submit();
}

/**
 * ボタン非活性
 */
window.disableClosingButtons = function () {
    // ボタン非活性
    $('#bulk-closing').prop('disabled', true);
    $('#bulk-cancel').prop('disabled', true);

    // ボタン非活性（一覧）
    $('button[name="single-closing"]').prop('disabled', true);
    $('button[name="single-cancel"]').prop('disabled', true);
    $('button[name="single-print"]').prop('disabled', true);
}

/**
 * ローディング切替(一括締処理）
 */
window.changeClosingLoading = function () {
    // ローディング切替
    $('#bulk-closing i').hide();
    $('#bulk-closing div').show();
}

/**
 * ローディング切替(一括解除）
 */
window.changeCancelLoading = function () {
    // ローディング切替
    $('#bulk-cancel i').hide();
    $('#bulk-cancel div').show();
}

/**
 * ローディング切替(個別解除）
 */
window.changeSingleLoading = function (target) {
    // ローディング切替
    $(target).children('div').show();
}

/**
 * ボタン非活性
 */
window.revertClosingButtons = function () {
    // ボタン非活性
    $('#bulk-closing').prop('disabled', false);
    $('#bulk-cancel').prop('disabled', false);

    // ボタン非活性（一覧）
    $('button[name="single-closing"]').prop('disabled', false);
    $('button[name="single-cancel"]').prop('disabled', false);
    $('button[name="single-print"]').prop('disabled', false);
}

/**
 * ローディング切替(一括締処理）
 */
window.revertClosingLoading = function () {
    // ローディング切替
    $('#bulk-closing i').show();
    $('#bulk-closing div').hide();
}
