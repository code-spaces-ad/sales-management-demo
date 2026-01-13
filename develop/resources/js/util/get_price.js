/**
 * 仕入先・商品毎の単価履歴
 */
window.getSupplierUnitPriceHistory = function (target) {
    let row = $(target).closest('tr');

    let supplier_id = $('.input-supplier-select option:selected').val();
    let product_id = row.find('.input-product-select option:selected').val();
    let count = 4;

    if( supplier_id === '' ||  product_id === '' ){
        return;
    }
    // 商品IDが異なる時のみ処理する
    if( $("#hidden_price_history_product_id").val() === product_id){
        return;
    }

    // ローディング表示
    $("#price_history_loading").show();

    axios.get(`/api/purchase_order/get_unit_price_history`, {
        params: {
            'supplier_id': supplier_id,
            'product_id': product_id,
            'count': count
        }
    })
        .then(response => {
            let price_list = response.data;
            let rows = document.querySelectorAll("#price_history tbody tr");

            // 全行クリア
            rows.forEach(row => {
                row.querySelector(".unit_price").innerHTML = '&nbsp;';
                row.querySelector(".order_date").innerHTML = '&nbsp;';
            });

            // データを順にセット
            price_list.forEach((item, index) => {
                if (index < rows.length) {
                    rows[index].querySelector(".unit_price").textContent = item.unit_price;
                    rows[index].querySelector(".order_date").textContent = item.order_date.replace(/-/g, "/");
                }
            });

            // 同一商品ロード対策
            $("#hidden_price_history_product_id").val(product_id);

        })
        .catch(error => {
            alert("単価履歴が取得できませんでした");
            console.error(error);
        })
        .finally(() => {
            // ローディング非表示
            $("#price_history_loading").hide();
        });
}

/**
 * 得意先・商品毎の単価履歴
 */
window.getCustomerUnitPriceHistory = function (target) {
    let row = $(target).closest('tr');

    let customer_id = $('.input-customer-select option:selected').val();
    let product_id = row.find('.input-product-select option:selected').val();
    let count = 4;

    if( customer_id === '' ||  product_id === '' ){
        return;
    }
    // 商品IDが異なる時のみ処理する
    if( $("#hidden_price_history_product_id").val() === product_id){
        return;
    }

    // ローディング表示
    $("#price_history_loading").show();

    axios.get(`/api/sales_order/get_unit_price_history`, {
        params: {
            'customer_id': customer_id,
            'product_id': product_id,
            'count': count
        }
    })
        .then(response => {
            let price_list = response.data;
            let rows = document.querySelectorAll("#price_history tbody tr");

            // 全行クリア
            rows.forEach(row => {
                row.querySelector(".unit_price").innerHTML = '&nbsp;';
                row.querySelector(".order_date").innerHTML = '&nbsp;';
            });

            // データを順にセット
            price_list.forEach((item, index) => {
                if (index < rows.length) {
                    rows[index].querySelector(".unit_price").textContent = item.unit_price;
                    rows[index].querySelector(".order_date").textContent = item.order_date.replace(/-/g, "/");
                }
            });

            // 同一商品ロード対策
            $("#hidden_price_history_product_id").val(product_id);

        })
        .catch(error => {
            alert("単価履歴が取得できませんでした");
            console.error(error);
        })
        .finally(() => {
            // ローディング非表示
            $("#price_history_loading").hide();
        });
}
