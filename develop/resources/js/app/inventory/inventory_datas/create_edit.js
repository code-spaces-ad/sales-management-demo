/**
 * フォーム変更フラグ
 * @type {boolean}
 */
let flgChangeForm = false;

/**
 * 画面遷移アラート
 *
 * @param event
 * @returns {string}
 */
let unloadHandler = function (event) {
    if (flgChangeForm) {
        event.preventDefault();
    }
};

/**
 * ロードイベントに追加
 */
window.addEventListener('load', function () {
    // 伝票日付にフォーカス
    $('#inout_date').focus();
    //  商品セレクトボックスチェンジイベント発火
    $('.input-product-select').change();
    // 倉庫セレクトボックスチェンジイベント発火
    $('.input-warehouse-code').change();
    $('.input-warehouse-code2').change();
    // 担当者セレクトボックスチェンジイベント発火
    $('.input-employee-select').change();
    // 金額再計算
    $('#inventory_data_table tbody tr').each(function () {
        // 商品コードセット
        setProductCode($(this));
    });

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;

    /**
     * イベント監視開始
     */
    $(window).on('beforeunload', unloadHandler);
    /**
     * イベント監視解除
     */
    $('#editForm').on('submit', function () {
        $(window).off('beforeunload', unloadHandler);
    });
    /**
     * フォーム変更イベント
     */
    $('#editForm').on('change', function () {
        flgChangeForm = true;
    });

    /**
     * 数量 フォーカスイベント
     */
    $(".input-quantity").focus(function () {
        // 桁区切りを一旦解除
        let quantity = $(this).val().replace(/,/g, '');
        $(this).val(quantity);
        // 全選択にする
        $(this).select();
    });

    /**
     * 数量 キーダウンイベント
     */
    $(".input-quantity").keydown(function (event) {
        // 「type='number'」は「e」が入力可能なので除外
        if (event.key === 'e') {
            return false;
        }
    });

    /**
     * 数量 ブラーイベント
     */
    $(".input-quantity").blur(function () {
        let quantityStr = $(this).val().replace(/[^\.0-9]/g, '');   // ※マイナス値不可
        if (quantityStr.length === 0) {
            quantityStr = '0';
        }

        let quantity = parseFloat(quantityStr);
        let digit = 0;
        let method = 3;
        let calcQuantity = getFloorValueForDigit(quantity, digit, method);

        // typeを元に戻す
        //$(this).get(0).type = 'text';
        // 数量セット
        $(this).val(calcQuantity.toLocaleString(undefined, {
            minimumFractionDigits: digit,
            maximumFractionDigits: digit
        }));
    });

    //数量桁区切りセット
    $('.input-quantity').map(function () {
        let quantity = $(this).closest('tr').find('.input-quantity').val().replace(/(\d)(?=(\d\d\d)+$)/g, '$1,');

        $(this).closest('tr').find('.input-quantity').val(quantity);
    })
});

/**
 * 桁数で切り捨てされた値を取得
 *
 * @param targetValue
 * @param digit
 * @param method
 * @returns {number|*}
 */
window.getFloorValueForDigit = function(targetValue, digit, method) {
    if (typeof digit === 'undefined') {
        return targetValue;
    }

    let coef = Math.pow(10, digit);
    // 切り捨て
    if (method === window.Laravel.enums.rounding_method_type.round_down) {
        return Math.floor(targetValue * coef) / coef;
    }
    // 切り上げ
    if (method === window.Laravel.enums.rounding_method_type.round_up) {
        return Math.ceil(targetValue * coef) / coef;
    }
    // 四捨五入
    if (method === window.Laravel.enums.rounding_method_type.round_off) {
        return Math.round(targetValue * coef) / coef;
    }
    return targetValue;
}

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();                // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア
    $('.input-inout-date').val($('#default_inout_date').val());
    $('.input-constr-site-select').prop('selectedIndex', 0).change();
    $('.input-warehouse-select').prop('selectedIndex', 0).change();
    $('.input-warehouse-select2').prop('selectedIndex', 0).change();
    $('.input-product-name').val('');
    $('.input-quantity').val('');
    $('.input-note').val('');
    $('.input-employee-select').prop('selectedIndex', 0).change();
    changeEmployee();
}

/**
 * クリア処理
 */
window.clearWarehouse = function(target) {
    let targetWarehouse = $(target).closest('div').find('.input-warehouse-select').val();

    if (targetWarehouse === "") {
        $('#inventory-stock-select-0').val('');
        $('#inventory-stock-select-1').val('');
        $('#inventory-stock-select-2').val('');
        $('#inventory-stock-select-3').val('');
        $('#inventory-stock-select-4').val('');
    }
}

/**
 * 対象の商品行クリア
 */
window.clearProduct = function(target) {
    $(target).closest('tr').find('.invalid-feedback').remove();                // エラーメッセージクリア
    $(target).closest('tr').find('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $(target).closest('tr').find('.input-product-code').val('');
    $(target).closest('tr').find('.input-product-select').prop('selectedIndex', 0).change();
    $(target).closest('tr').find('.input-product-name').val('');
    $(target).closest('tr').find('.input-warehouse-code').val('');
    $(target).closest('tr').find('.input-warehouse-code2').val('');
    $(target).closest('tr').find('.input-warehouse-select').prop('selectedIndex', 0).change();
    $(target).closest('tr').find('.input-warehouse-select2').prop('selectedIndex', 0).change();
    $(target).closest('tr').find('.input-quantity').val('');
    $(target).closest('tr').find('.input-note').val('');
    $(target).closest('tr').find('.' + $(target).closest('tr').find($('[id^=inventory-stock-select-]')).attr('class')).val('');
    $(target).closest('tr').find('.inventory-stock-select').val(0);
}

/**
 * モーダル変更後の対象行を保存
 */
window.modalTargetRow = function(row_no) {
    $('#modal_target_row').val(row_no);
}

/**
 * 担当者コード変更時の処理
 * @param target
 */
window.changeEmployeeCode = function(target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-employee-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-employee-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 担当者セレクトボックス変更処理
 */
window.changeEmployee = function () {
    let code = $('.input-employee-select option:selected').data('code');
    // 選択された担当者のコードをセット
    $('.input-employee-code').val(code);
}

/** 移動元 */
/**
 * from倉庫コード変更時の処理
 * @param target
 */
window.changeWarehouseCode = function(target) {
    let targetValue = $(target).val();
    if (targetValue === '') {
        return;
    }
    if (isNaN(targetValue)) {
        targetValue = -1;
    }
    let targetOption = ".input-warehouse-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-warehouse-select').prop('selectedIndex', 0).change();
    }
}

/**
 * from倉庫セレクトボックス変更処理
 * @param target
 */
window.changeWarehouse = function(target) {
    let code = $('.input-warehouse-select option:selected').data('code');
    // 選択された担当者のコードをセット
    $('.input-warehouse-code').val(code);

    //from倉庫選択
    filterSeleWarehouseInventoryDataDetails();

    // クリア処理
    clearWarehouse(target);
}


let warehouseTurn;

window.selectWarehouseTurn = function(key) {
    warehouseTurn = key;
}

/** 移動先 */
/**
 * to倉庫コード変更時の処理
 * @param target
 */
window.changeWarehouseCode2 = function(target) {
    let targetValue = $(target).val();
    if (isNaN(targetValue)) {
        targetValue = -1;
    }
    let targetOption = ".input-warehouse-select2 option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-warehouse-select2').prop('selectedIndex', 0).change();
    }
}

/**
 * to倉庫セレクトボックス変更処理
 * @param target
 */
window.changeWarehouse2 = function(target) {
    let code = $('.input-warehouse-select2 option:selected').data('code');
    // 選択された担当者のコードをセット
    $('.input-warehouse-code2').val(code);
}

/**
 * 商品コード変更時の処理
 * @param target
 */
window.changeProductCodeCreateEdit = function(target) {
    let row = $(target).closest('tr');
    let targetValue = row.find('.input-product-code').val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-product-select option[data-code='" + parseInt(targetValue) + "']";
    if (row.find(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        row.find(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        row.find('.input-product-select').prop('selectedIndex', 0).change();
        row.find('.input-product-code').val('');
        clearProduct(row);
        row.find('.input-product-code').focus();
        alert('商品が見つかりません。\r\n商品コード：' + parseInt(targetValue));
        $(target).closest('tr').find('.' + $(target).closest('tr').find($('[id^=inventory-stock-select-]')).attr('class')).val('');
        return;
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');

    // 選択されたコードの商品名をセット
    let name = row.find('.input-product-select option:selected').data('name');
    row.find('.input-product-name').val(name);

}

/**
 * 商品セレクトボックス変更処理
 * @param target
 */
window.changeProductCreateEdit = function(target) {
    let row = $(target).closest('tr');
    let name = row.find('.input-product-select option:selected').data('name');

    let classname = (row.find($('[id^=inventory-stock-select-]')).attr('id'));
    let productid = row.find('.input-product-select option:selected').val();
    if (typeof name === 'undefined') {
        // 選択されたコードのコードをクリア
        row.find('.input-product-code').val('');
        row.find('.input-product-name').val('');
        return;
    }

    // 選択されたコードの商品名をセット
    row.find('.input-product-name').val(name);

    let code = row.find('.input-product-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された商品のコードをセット
    $(target).closest('tr').find('.input-product-code').val(code);

    //支所セレクトボックスフィルタリング
    filterSeleProductInventoryDataDetails(classname, productid);
}

/**
 * 商品検索モーダル用 商品選択
 * @param target
 */
window.selectProductSearchProductModal = function(target) {
    let targetValue = $(target).data('code');

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    // 伝票の対象行の倉庫コードを変更
    let row_no = $('#modal_target_row').val();
    let target_row = $('#inventory_data_table tbody tr').eq(row_no);
    let targetOption = ".input-product-select option[data-code='" + parseInt(targetValue) + "']";

    target_row.find('.input-product-code').val(targetValue);
    if (target_row.find(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        target_row.find(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        target_row.find('.input-product-select').prop('selectedIndex', 0).change();
    }

    // 商品変更時の処理
    changeProductCreateEdit(target_row);

    // 商品検索モーダルを閉じる
    $('#search-product').modal('hide');

    // フォーカス移動ON
    flgChangeFocusSearchProduct = true;
}

/**
 * 在庫データフィルタリング処理(商品選択時)
 */
window.filterSeleProductInventoryDataDetails = function(target, product) {
    let targetWarehouseId = $('.input-warehouse-select option:selected').val();

    if (product && targetWarehouseId) {
        $("#" + target + " option").each(function () {
            if ($(this).data('product_id') === parseInt(product) && $(this).data('warehouse_id') === parseInt(targetWarehouseId)) {
                // 得意先IDが不一致は非表示セット
                $(this).prop('selected', true);
            } else {
                $(this).prop('selected', false);
            }
        });
    }
}

/**
 * 在庫データフィルタリング処理(from倉庫選択時)
 */
window.filterSeleWarehouseInventoryDataDetails = function () {
    let targetWarehouseId = $('.input-warehouse-select option:selected').val();

    let product_code = [];
    let product_id = [];

    for (let i = 0; i < 5; i++) {
        product_code[i] = $('#product_code-' + i).val();
        product_id[i] = $('#product_id-' + i + ' option:selected').data('id');
    }

    $.each(product_code, function (key) {
        if (product_code[key] && targetWarehouseId) {
            $('#inventory-stock-select-' + key + ' option').each(function () {
                if ($(this).data('product_id') === parseInt(product_id[key]) && $(this).data('warehouse_id') === parseInt(targetWarehouseId)) {
                    $(this).prop('selected', true);
                } else {
                    $(this).prop('selected', false);
                }
            });
        }
    });
}

/**
 * 商品コードセット処理
 * @param target
 */
window.setProductCode = function(target) {
    let code = $(target).closest('tr').find('.input-product-select option:selected').data('code');

    // 選択されたコードをセット
    $(target).closest('tr').find('.input-product-code').val(code);
}

/**
 * 商品単位名セット処理
 * @param target
 */
window.setProductUnitName = function(target) {
    let unitName = $(target).closest('tr').find('.input-product-select option:selected').data('unit-name');
}

/**
 * 現在個数セット処理
 * @param target
 */
window.setInventoryStocks = function(target) {

    let row = $(target).closest('tr');
    let productId = row.find('.input-product-select option:selected').val();

    let lastPurchaseUnitPrice = $("#customers_products_list option[data-id='" + customerId + "']"
        + "[data-id='" + productId + "']").val();

    if (lastPurchaseUnitPrice !== undefined) {
        // 一致するデータがあれば、選択された商品の最終単価をセット
        row.find('.input-quantity').val(purchaseUnitPrice);
    } else {
        // 一致するデータがなければ、選択された商品の単価をセット
        row.find('.input-quantity').val(purchaseUnitPrice);
    }
}

/**
 * 編集フォームSubmit処理
 * @returns {boolean}
 */
window.editFormSubmit = function () {
    let data = '';

    $('#inventory_data_table tr').each(function (index) {
        let productName = $(this).find('.input-product-name').val();
        let quantity = $(this).find('.input-quantity').val();

        if (!productName && !quantity) {
            // continue の代わり
            return true;
        }

        // 伝票詳細データを別でセット
        data += `
            <input type="hidden" name="detail[${index}][product_id]" value="${$(this).find('.input-product-select').val()}">
            <input type="hidden" name="detail[${index}][product_name]" value="${productName}">
            <input type="hidden" name="detail[${index}][quantity]" value="${quantity}">
            <input type="hidden" name="detail[${index}][note]" value="${$(this).find('.input-note').val()}">
        `;
    });

    return true;
}

/**
 * 登録更新処理
 */
window.store = function () {
    // モーダル閉じる
    $('#confirm-store').modal('hide');

    $('#inventory_data_table tbody tr').each(function () {
        let closest_td = $(this).children('td');
        //数量(桁区切り無し)
        let quantity = $(this).find('.input-quantity').val().replace(/,/g, '');

        $(this).find('.input-quantity').val(quantity);

        //商品コード未入力行をdisabled
        if (!$(this).find('.input-product-code').val()) {
            closest_td.find('*').prop('disabled', 'true');
            $(this).find('.input-sort').prop('disabled', 'true');
            $(this).find('.input-checked-sales-confirm').prop('disabled', 'true');
        }
    });

    // ボタン非活性
    disableButtons();
    // ローディング切替
    changeStoreLoading();

    // submit形成
    editFormSubmit();

    // submit処理
    $('#editForm').submit();
}

/**
 * 削除処理
 */
window.destory = function () {
    // モーダル閉じる
    $('#confirm-delete').modal('hide');

    // ボタン非活性
    disableButtons();
    // ローディング切替
    changeDeleteLoading();

    // submit処理
    $('#deleteForm').submit();
}

/**
 * 複製処理
 */
window.copy = function (route) {
    // モーダル閉じる
    $('#confirm-copy').modal('hide');

    $('#inventory_data_table tbody tr').each(function () {
        let closest_td = $(this).children('td');
        //数量(桁区切り無し)
        let quantity = $(this).find('.input-quantity').val().replace(/,/g, '');

        $(this).find('.input-quantity').val(quantity);

        //商品コード未入力行をdisabled
        if (!$(this).find('.input-product-code').val()) {
            closest_td.find('*').prop('disabled', 'true');
            $(this).find('.input-sort').prop('disabled', 'true');
            $(this).find('.input-checked-sales-confirm').prop('disabled', 'true');
        }
    });

    // ボタン非活性
    disableButtons();
    // ローディング切替
    changeCopyLoading();

    // submit形成
    editFormSubmit();

    // submit前の書換処理
    $('#copy_number').val($('#order_number').val());
    $('#editForm').attr('action', route);
    $('input:hidden[name="_method"]').val("POST");

    // submit処理
    $('#editForm').submit();
}
