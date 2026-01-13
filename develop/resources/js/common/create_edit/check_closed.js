/**
 * 締処理済みチェック(売上)
 */
window.checkChargeClosed = function (billing_bool = false) {
    let route = $('.hidden-api-is-closing-url').val();
    let customer_id = $('.input-customer-select option:selected').val();
    let order_date = $('#billing_date').val();

    if (billing_bool) {
        order_date = $('#order_date').val();
    }

    if (customer_id === '' || order_date === '') {
        $('[data-target="#confirm-store"]').prop('disabled', true);
        $('[data-target="#confirm-delete"]').prop('disabled', true);
        $('[data-target="#order-label"]').hide();
        return;
    }

    // 現金の場合は登録可とする
    let transaction_type_id = $('.input-transaction-type-select option:selected').val();
    if (transaction_type_id === '1') {
        $('[data-target="#confirm-store"]').prop('disabled', false);
        $('[data-target="#confirm-store-showpdf"]').prop('disabled', false);
        $('[data-target="#confirm-delete"]').prop('disabled', false);
        $('[data-target="#confirm-copy"]').prop('disabled', false);
        $('[data-target="#order-label"]').hide();
        return;
    }

    // 以後、売掛伝票の場合
    let billing_date = $('#billing_date').val();
    if (customer_id === '' || billing_date === '') {
        $('[data-target="#confirm-store"]').prop('disabled', true);
        $('[data-target="#confirm-store-showpdf"]').prop('disabled', true);
        $('[data-target="#confirm-delete"]').prop('disabled', true);
        $('[data-target="#confirm-copy"]').prop('disabled', true);
        $('[data-target="#order-label"]').hide();
        return;
    }
    // 指定の得意先と年月で締処理があるかを取得(API非同期通信)
    axios.get(route, {
        params: {
            customer_id: customer_id,
            charge_date: order_date,
        }
    }).then(response => {
        // 正常処理時
        $('[data-target="#confirm-store"]').prop('disabled', response.data[0]);
        $('[data-target="#confirm-store-showpdf"]').prop('disabled', response.data[0]);
        $('[data-target="#confirm-delete"]').prop('disabled', response.data[0]);
        $('[data-target="#confirm-copy"]').prop('disabled', response.data[0]);
        response.data[0] ? $('[data-target="#order-label"]').show() : $('[data-target="#order-label"]').hide();
    }).catch(error => {
        // エラー時
        console.error(error);
    });
}

/**
 * 締処理済みチェック(仕入)
 */
window.checkPurchaseClosed = function (order_date = false) {
    let route = $('.hidden-api-is-purchase-closing-url').val();
    // 以後、買掛伝票の場合
    let supplier_id = $('.input-supplier-select option:selected').val();
    let closing_date = $('#closing_date').val();

    if (order_date) {
        closing_date = $('#order_date').val();
    }

    if (supplier_id === '' || closing_date === '') {
        $('[data-target="#confirm-store"]').prop('disabled', true);
        $('[data-target="#confirm-delete"]').prop('disabled', true);
        $('[data-target="#confirm-copy"]').prop('disabled', true);
        $('[data-target="#order-label"]').hide();
        return;
    }

    // 現金の場合は登録可とする
    let transaction_type_id = $('.input-transaction-type-select option:selected').val();
    if (transaction_type_id === '1') {
        $('[data-target="#confirm-store"]').prop('disabled', false);
        $('[data-target="#confirm-store-showpdf"]').prop('disabled', false);
        $('[data-target="#confirm-delete"]').prop('disabled', false);
        $('[data-target="#confirm-copy"]').prop('disabled', false);
        $('[data-target="#order-label"]').hide();
        return;
    }

    // 指定の得意先と年月で締処理があるかを取得(API非同期通信)
    axios.get(route, {
        params: {
            supplier_id: supplier_id,
            closing_date: closing_date,
        }
    }).then(response => {
        // 正常処理時
        $('[data-target="#confirm-store"]').prop('disabled', response.data[0]);
        $('[data-target="#confirm-delete"]').prop('disabled', response.data[0]);
        $('[data-target="#confirm-copy"]').prop('disabled', response.data[0]);
        response.data[0] ? $('[data-target="#order-label"]').show() : $('[data-target="#order-label"]').hide();

        if (!response.data[0]) {
            //　チェックPOS
            checkPos();
        }

    }).catch(error => {
        // エラー時
        console.error(error);
    });
}
