/**
 * loadイベントに追加
 */
window.addEventListener('load', function () {
    // 「商品コード」にフォーカス
    $('.input-product-code').focus();

    // 編集画面へのリンクを取得
    const edit_link = SetLink();
    // ナビゲーションのリンクを取得
    const nav_link = SetNavLink();

    //仕入と納品を非表示
    $('[data-name="仕入"]').addClass('d-none');
    $('[data-name="納品"]').addClass('d-none');

    //在庫調整フラグのvalue書き換え
    SetAdjustStocks();
    //リンク切り替え
    SwitchProductLink();
    //リンク書き換え
    EditLink(edit_link, nav_link);
});

/**
 * リンク書き換え
 */
function EditLink(product, nav) {
    /**
     * 商品名のリンク書き換え
     */
    $("#adjust_stocks_switch").on("click", function () {
        //在庫調整フラグのvalue書き換え
        SetAdjustStocks();

        //リンク切り替え
        SwitchProductLink();
    });

    /**
     * ページネーションのリンク書き換え
     */
    $(".page-link").on("click", function () {
        let page_link = $(this).attr('href');
        let key = $.inArray(page_link, nav);

        if (nav[key].indexOf("adjust_stocks") !== -1) {
            let page_link = nav[key].replace(/(adjust_stocks=)(\d)/, "adjust_stocks=" + $("#adjust_stocks_switch").val());
            //urlパラメータをセット
            $(this).attr('href', page_link);

            return;
        }
        //urlパラメータをセット
        $(this).attr('href', nav[key] + "&adjust_stocks=" + $("#adjust_stocks_switch").val());
    });
}

/**
 * リンク(URL)のセット
 */
function SetLink() {
    let link = [];

    $('#stocks_data_table tbody tr').each(function () {
        let product_class = $(this).find('.product_name').attr('id');

        link.push($("#" + product_class).attr('href'));
    })
    return link;
}

/**
 * ナビゲーションのリンク(URL)のセット
 */
function SetNavLink() {
    let link = [];

    $('.pagination li').each(function () {
        link.push($(this).find('.page-link').attr('href'));
    })
    return link;
}

/**
 * 商品名のリンク切り替え
 */
function SwitchProductLink() {
    $('#stocks_data_table tbody tr').each(function () {
        let product_class = $(this).find('.product_name').attr('id');
        let inventory_class = $(this).find('.inventory_stock').attr('id');

        //リンク有効
        $("#" + product_class).off("click", myHandler);

        if ($("#" + inventory_class).text() == 0 && $("#adjust_stocks_switch").prop('checked')) {
            //リンク無効
            $("#" + product_class).on("click", myHandler);
        }
    })
}

/**
 * 在庫調整フラグのvalue書き換え
 */
function SetAdjustStocks() {
    const adjust_stocks_switch = $("#adjust_stocks_switch");
    if (adjust_stocks_switch.prop('checked')) {
        adjust_stocks_switch.val(1);

        return;
    }
    //value書き換え
    adjust_stocks_switch.val(0);
}

/**
 * アンカータグを無効
 */
function myHandler(e) {
    e.preventDefault();
}
