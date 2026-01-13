window.adjustInventoryValue = function (target) {
    let wareHouseId = $(target).data('warehouse-id');
    let productId = $(target).data('product-id');
    let inventory = $(target).val();
    let stockError = $(target).next('div');
    let url = $('input[name=post_url]').val();
    let key = $(target).attr('id').replace("stocks","");
    $(stockError).removeClass('display-none');
    $(target).removeClass('border-red');

    // 入力された値が数字で無ければ、エラーメッセージを表示し、return
    if (!$.isNumeric(inventory)) {
        $(target).addClass('border-red');
        return;
    }
    $(stockError).addClass('display-none');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });
    $.ajax({
        type: 'post',
        url: url,
        dataType: 'json',
        data: {
            warehouse_id: wareHouseId,
            product_id: productId,
            inventory_value: inventory,
        },
    }).done(function (response) {
        // 更新されたcsrfトークンをセット
        $('meta[name="csrf-token"]').attr('content', response['token']);
        document.getElementById('total_purchase' + key).innerText = Intl.NumberFormat('ja-JP').format(response['purchase_total_price']);
    }).fail(function (xhr) {
        console.log(xhr);
    });
}
