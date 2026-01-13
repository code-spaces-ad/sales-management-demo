window.adjustStocksPost = function (target) {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $.ajax({
        type: "post",
        url: "/ajax/inventory_stock_data",
        dataType: "json",
        data: {
            adjust_stocks: $(target).val(),
        },
    });
}
