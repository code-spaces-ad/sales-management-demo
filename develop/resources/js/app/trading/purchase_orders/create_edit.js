/**
 * 得意先検索モーダルが閉じた後のフォーカス移動判定フラグ
 * @type {boolean}
 */
let flgChangeFocusSearchCustomer = false;
/**
 * 商品検索モーダルが閉じた後のフォーカス移動判定フラグ
 * @type {boolean}
 */
let flgChangeFocusSearchProduct = false;
/**
 * 業者（仕入先）検索モーダルが閉じた後のフォーカス移動判定フラグ
 * @type {boolean}
 */
let flgChangeFocusSearchSupplier = false;

/**
 * フォーム変更フラグ
 * @type {boolean}
 */
let flgChangeForm = false;

/**
 * 画面遷移アラート
 *
 * @returns {string}
 */
let unloadHandler = function (event) {
    if (flgChangeForm) {
        event.preventDefault();
    }
};

/**
 * フォーム読み込み時に処理
 */
window.onload = function () {
    // 締処理済みチェック
    checkPurchaseClosed();
}

/**
 * ロードイベントに追加
 */
window.addEventListener('load', function () {
    /**
     * 行ソート処理
     */
    $('#sortdata').sortable();
    // sortstopイベントをバインド
    $('#sortdata').bind('sortstop', function () {
        // 番号を設定している要素に対しループ処理
        $(this).find('.row-number').each(function (idx) {
            $(this).html(idx + 1);
        });
    });
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

    // 初期表示用
    changeDepartment();
    changeOfficeFacility();
    getTargetDateTaxRate($('#order_date').val());

    // 伝票日付にフォーカス
    $('#order_date').focus();

    $('#order_products_table tbody tr').each(function () {
        // 商品コードセット
        setProductCode($(this));
        // 数量桁区切り処理セット
        setQuantityNumberFormat($(this));
        // 値引桁区切りセット
        setDiscountNumberFormat($(this));
        setOrderDiscountNumberFormat();
        // 単価桁区切り処理セット
        setUnitPriceNumberFormat($(this));
        // 金額端数処理セット
        setAmountRoundingMethod($(this));
        // 税区分セット
        setProductTaxType($(this));
        // 金額再計算
        setProductPrice($(this));
    });

    // 各合計値セット
    setTotalAmounts();

    // 初期化
    flgChangeForm = $('.hidden-errors-any').val() ? true : false;

    /**
     * 数量 フォーカスイベント
     */
    $(".input-quantity").focus(function () {
        // 桁区切りを一旦解除
        let quantity = $(this).val().replace(/,/g, '');
        $(this).val(quantity);

        // type変更
        //$(this).get(0).type = 'number';

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
    $("#order_products_table tbody").on('blur', '.input-quantity', function () {
        let quantityStr = $(this).val().replace(/[^\-.0-9]/g, '');   // ※マイナス値可
        if (quantityStr.length === 0) {
            quantityStr = '0';
        }

        let quantity = parseFloat(quantityStr);
        let digit = $(this).closest('tr').find('.input-product-select option:selected').data('quantity-decimal-digit');
        let method = $(this).closest('tr').find('.input-product-select option:selected').data('quantity-rounding-method-id');
        let calcQuantity = getFloorValueForDigit(quantity, digit, method);

        // typeを元に戻す
        //$(this).get(0).type = 'text';
        // 数量セット
        $(this).val(calcQuantity.toLocaleString(undefined, {
            style: "decimal",
            useGrouping: true,
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        }));

        // 金額再セット
        setProductPrice($(this));
        // 各合計値セット
        setTotalAmounts();
    });

    /**
     * 単価 フォーカスイベント
     */
    $(".input-unit-price").focus(function () {
        // 桁区切りを一旦解除
        let unitPrice = $(this).val().replace(/,/g, '');
        $(this).val(unitPrice);

        // type変更
        $(this).get(0).type = 'number';

        // 全選択にする
        $(this).select();
    });

    /**
     * 単価 キーダウンイベント
     */
    $(".input-unit-price").keydown(function (event) {
        // 「type='number'」は「e」が入力可能なので除外
        if (event.key === 'e') {
            return false;
        }
    });

    /**
     * 単価 ブラーイベント
     */
    $("#order_products_table tbody").on('blur', '.input-unit-price', function () {
        let unitPriceStr = $(this).val().replace(/[^\-\.0-9]/g, '');    // ※マイナス値可
        if (unitPriceStr.length === 0) {
            unitPriceStr = '0';
        }

        let unitPrice = parseFloat(unitPriceStr);
        let digit = $(this).closest('tr').find('.input-product-select option:selected').data('unit-price-decimal-digit');
        let method = $(this).closest('tr').find('.input-product-select option:selected').data('amount-rounding-method-id');
        let calcUnitPrice = getFloorValueForDigit(unitPrice, digit, method);

        // typeを元に戻す
        $(this).get(0).type = 'text';
        // 単価セット
        $(this).val(calcUnitPrice.toLocaleString(undefined, {
            style: "decimal",
            useGrouping: true,
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        }));

        // 金額再セット
        setProductPrice($(this));
        // 各合計値セット
        setTotalAmounts();
    });

    /**
     * 値引額 フォーカスイベント
     */
    $(".input-discount").focus(function () {
        // 桁区切りを一旦解除
        let discount = $(this).val().replace(/,/g, '');
        $(this).val(discount);

        // type変更
        $(this).get(0).type = 'number';

        // 全選択にする
        $(this).select();
    });

    /**
     * 値引額 キーダウンイベント
     */
    $(".input-discount").keydown(function (event) {
        // 「type='number'」は「e」が入力可能なので除外
        if (event.key === 'e') {
            return false;
        }
    });

    /**
     * 明細：値引額 ブラーイベント
     */
    $("#order_products_table tbody").on('blur', '.input-discount', function () {
        let discountStr = $(this).val().replace(/[^\-\.0-9]/g, '');    // ※マイナス値可
        if (discountStr.length === 0) {
            discountStr = '0';
        }

        let discount = parseFloat(discountStr);
        let digit = $(this).closest('tr').find('.input-product-select option:selected').data('unit-price-decimal-digit');
        let method = $(this).closest('tr').find('.input-product-select option:selected').data('amount-rounding-method-id');
        let calcDiscount = getFloorValueForDigit(discount, digit, method);

        // typeを元に戻す
        $(this).get(0).type = 'text';
        // 単価セット
        $(this).val(calcDiscount.toLocaleString(undefined, {
            style: "decimal",
            useGrouping: true,
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        }));

        // 金額再セット
        setProductPrice($(this));
        // 各合計値セット
        setTotalAmounts();
    });

    /**
     * 値引額 ブラーイベント
     */
    $(".input-order-discount").blur(function () {
        let discountStr = $(this).val().replace(/[^\-\.0-9]/g, '');    // ※マイナス値可
        if (discountStr.length === 0) {
            discountStr = '0';
        }

        let discount = parseFloat(discountStr);
        let digit = 0;
        let method = 1;
        let calcDiscount = getFloorValueForDigit(discount, digit, method);

        // typeを元に戻す
        $(this).get(0).type = 'text';
        // 単価セット
        $(this).val(calcDiscount.toLocaleString(undefined, {
            style: "decimal",
            useGrouping: true,
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        }));

        // // 金額再セット
        // setProductPrice($(this));
        // 各合計値セット
        setTotalAmounts();
    });

    /**
     * 税率 ブラーイベント
     */
    $("#order_products_table tbody").on('blur', '.input-consumption-tax-rate-select', function () {
        $(this).closest('tr').find('.hidden-consumption-tax-rate').val(
            $(this).closest('tr').find('.input-consumption-tax-rate-select').val()
        );

        // 税区分セット
        setProductTaxType($(this));
        // 商品金額再セット
        setProductPrice($(this));
        // 各合計値セット
        setTotalAmounts();
    });


    /**
     * 得意先検索ボタンフォーカスインイベント
     */
    $('[data-target="#search-customer"]').on('focusin', function () {
        if (flgChangeFocusSearchCustomer) {
            // 得意先リストにフォーカス移動
            $('.input-supplier-select').focus();
            // フラグをOFF
            flgChangeFocusSearchCustomer = false;
        }
    });

    /**
     * 商品検索ボタンフォーカスインイベント
     */
    $('[data-target="#search-product"]').on('focusin', function () {
        if (flgChangeFocusSearchProduct) {
            // 対象行の商品名入力にフォーカス移動
            $(this).closest('tr').find('.input-product-name').focus();
            // フラグをOFF
            flgChangeFocusSearchProduct = false;
        }
    });

    /**
     * 業者（仕入先）検索ボタンフォーカスインイベント
     */
    $('[data-target="#search-supplier"]').on('focusin', function () {
        if (flgChangeFocusSearchSupplier) {
            // 対象行の部門リストにフォーカス移動
            $(this).closest('tr').find('.input-supplier-select').focus();
            // フラグをOFF
            flgChangeFocusSearchSupplier = false;
        }
    });

    /**
     * 得意先コードキーダウンインイベント
     */
    $('.input-customer-code').keydown(function (event) {
        let code = event.code;
        // スペース押下時、かつ未入力の場合
        if (code === 'Space' && $(this).val().length === 0) {
            // 得意先検索ボタンクリック
            $('[data-target="#search-customer"]').click();
            return false;
        }
    });

    /**
     * 商品コードキーダウンインイベント
     */
    $('.input-product-code').keydown(function (event) {
        let code = event.code;
        // スペース押下時、かつ未入力の場合
        if (code === 'Space' && $(this).val().length === 0) {
            // 商品検索ボタンクリック
            $(this).closest('tr').find('[data-target="#search-product"]').click();
            return false;
        }
    });

    /**
     * 業者（仕入）コードキーダウンインイベント
     */
    $('.input-supplier-code').keydown(function (event) {
        let code = event.code;
        // スペース押下時、かつ未入力の場合
        if (code === 'Space' && $(this).val().length === 0) {
            // 部門検索ボタンクリック
            $(this).closest('tr').find('[data-target="#search-supplier"]').click();
            return false;
        }
    });

    $('#return').removeClass('back_inactive');
    $('#return').addClass('back_active');
});


/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();                // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-order-date').val($('#default_order_date').val());

    $('.input-supplier-select').prop('selectedIndex', 0).change();
    changeSupplierCreateEdit();
    $('.input-order-status-select').prop('selectedIndex', 0).change();

    $('.input-product-code').val('');
    $('.input-product-select').val('');
    $('.input-product-name').val('');
    $('.input-quantity').val('');
    $('.input-unit-name-select').prop('selectedIndex', 0).change();
    $('.input-unit-price').val('');
    $('.input-tax-rate-select').prop('selectedIndex', 0).change();
    $('.input-product-sub-total').val('0');
    $('.input-warehouse-code').val('');
    $('.input-warehouse-select').prop('selectedIndex', 0).change();
    $('.input-detail-note').val('');

    let default_tax_rate = $('#tax_rate').val();
    $('.input-consumption-tax-rate-select').val(default_tax_rate);
    $('.hidden-consumption-tax-rate').val('');
    $('.hidden-tax').val('0');
    $('.hidden-tax-calc-type-id').val('');
    $('.hidden-tax-type-id').val('');
    $('.hidden-reduced-tax-flag').val('');
    $('.hidden-tax-rounding-method-id').val('');
    $('.hidden-amount-rounding-method-id').val('');

    // 各合計値クリア
    $('.text-inctax-total').text('0');
    $('.text-gross-total').text('0');
    $('.text-sub-total-out-discount').text('0');
    $('.text-order-discount').text('0');
    $('.text-sub-total').text('0');
    $('.text-consumption-total').text('0');
    $('.text-reduced-total').text('0');
    $('.text-notax-total').text('0');
    $('.text-consumption-tax').text('0');
    $('.text-reduced-tax').text('0');

    // 各種税額（登録用の隠し項目）
    $('.hidden-purchase-total').val('0');
    $('.hidden-purchase-total-normal-out').val('0');
    $('.hidden-purchase-total-reduced-out').val('0');
    $('.hidden-purchase-total-normal-in').val('0');
    $('.hidden-purchase-total-reduced-in').val('0');
    $('.hidden-purchase-total-free').val('0');
    $('.hidden-purchase-tax-normal-out').val('0');
    $('.hidden-purchase-tax-reduced-out').val('0');
    $('.hidden-purchase-tax-normal-in').val('0');
    $('.hidden-purchase-tax-reduced-in').val('0');

    $('.text-purchase-total-normal-out').text('&yen;0');
    $('.text-purchase-total-reduced-out').text('&yen;0');
    $('.text-purchase-total-normal-in').text('&yen;0');
    $('.text-purchase-total-reduced-in').text('&yen;0');
    $('.text-purchase-total-free').text('&yen;0');
    $('.text-purchase-tax-normal-out').text('&yen;0');
    $('.text-purchase-tax-reduced-out').text('&yen;0');
    $('.text-purchase-tax-normal-in').text('&yen;0');
    $('.text-purchase-tax-reduced-in').text('&yen;0');

    // 締処理済みチェック
    checkPurchaseClosed();
}

/**
 * モーダルクローズ
 */
window.modalClose = function (target) {
    $(target).modal('hide');
}

/**
 * 編集フォームSubmit処理
 * @returns {boolean}
 */
window.editFormSubmit = function () {
    // 各金額項目から桁区切りをはずしておく
    $("[class*='input-discount']").each(function (index) {
        let amount = $(this).val().replace(/,/g, '');
        $(this).val(amount);
    });

    $("[class*='input-quantity']").each(function (index) {
        let amount = $(this).val().replace(/,/g, '');
        $(this).val(amount);
    });

    $("[class*='input-unit-price']").each(function (index) {
        let amount = $(this).val().replace(/,/g, '');
        $(this).val(amount);
    });

    return true;
}

/**
 * 対象の商品行クリア
 */
window.clearProduct = function (target) {
    $(target).closest('tr').find('.invalid-feedback').remove();                // エラーメッセージクリア
    $(target).closest('tr').find('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    let row = $(target).closest('tr');

    row.find('.invalid-feedback').remove();                // エラーメッセージクリア
    row.find('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    row.find('.input-product-code').val('');
    row.find('.input-product-select').prop('selectedIndex', 0).change();
    row.find('.input-product-name').val('');
    row.find('.input-quantity').val('');
    row.find('.input-unit-price').val('');
    row.find('.input-discount').val('');
    row.find('.input-warehouse-code').val('');
    row.find('.input-warehouse-select').prop('selectedIndex', 0).change();
    row.find('.input-detail-note').val('');
    let default_tax_rate = $('#tax_rate').val();
    row.find('.input-consumption-tax-rate-select').val(default_tax_rate);

    row.find('.hidden-consumption-tax-rate').val('');
    row.find('.hidden-tax').val('0');
    row.find('.hidden-tax-calc-type-id').val('');
    row.find('.label-tax-type-name').text('[税抜]');
    row.find('.hidden-tax-type-id').val('');
    row.find('.hidden-reduced-tax-flag').val('');
    row.find('.hidden-tax-rounding-method-id').val('');
    row.find('.hidden-amount-rounding-method-id').val('');

    // 商品金額再セット
    setProductPrice(target);
    // 各合計値セット
    setTotalAmounts();
}

/**
 * モーダル変更後の対象行を保存
 */
window.modalTargetRow = function (row_no) {
    $('#modal_target_row').val(row_no);
}

/**
 * 伝票日付変更時の処理
 * @param target
 */
window.changeOrdarDate = function (target) {
    $('#order_date').val($(target).val());

    // 締処理済みチェック
    checkPurchaseClosed();
}

/**
 * 伝票日付変更時の処理
 * @param target
 */
window.blurOrderDate = function (target) {
    // 対象日付の税率を取得
    getTargetDateTaxRate($(target).val());
}

/**
 * 仕入先コード変更時の処理
 * @param target
 */
window.changeSupplierCodeCreateEdit = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-supplier-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-supplier-select').prop('selectedIndex', 0).change();
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');

    // 仕入先セレクトボックス変更処理
    changeSupplierCreateEdit();
}

/**
 * 仕入先セレクトボックス変更処理
 */
window.changeSupplierCreateEdit = function () {
    let code = $('.input-supplier-select option:selected').data('code');

    function zeroPad(num, places) {
        let zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    let zero_fill_code = code;
    if (code) {
        zero_fill_code = zeroPad(code, 8);
    }

    // 選択された得意先名のコードをセット
    $('.input-supplier-code').val(zero_fill_code);

    // 選択された得意先の税計算区分をセット
    setTaxCalcType();
    // 選択された得意先の端数処理をセット
    setTaxRoundingMethod();

    $('#order_products_table tbody tr').each(function () {
        // 金額再計算
        setProductPrice($(this));
    });
    // 各合計値セット
    setTotalAmounts();

    // 締処理済みチェック
    checkPurchaseClosed();
}

/**
 * 商品コード変更時の処理
 * @param target
 */
window.changeProductCodeCreateEdit = function (target) {

    let row = $(target).closest('tr');
    let targetValue = row.find('.input-product-code').val();

    if (targetValue === '') {
        return;
    }
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
        alert('商品が見つかりません。\r\n商品コード：' + parseInt(targetValue));
        return;
    }

    // 選択されたコードの商品名をセット
    let name = row.find('.input-product-select option:selected').data('name');
    row.find('.input-product-name').val(name);

    //select2チェンジイベント
    $('.select2_search').trigger('change');

    // 商品単位名セット
    setProductUnitName(target);
    // 税率セット
    setProductConsumptionTaxRate(target);
    // 税区分セット
    setProductTaxType(target);
    // 金額端数処理セット
    setAmountRoundingMethod(target);
    // 税額端数処理セット（得意先単位）
    setTaxRoundingMethod();
    // 単価履歴セット
    setUnitPriceHistory(target);
    // 単価小数桁数セット
    setUnitPriceDecimalDigit(target);
    // 単価桁区切りセット
    setUnitPriceNumberFormat(target);
    // 数量小数桁数セット
    setQuantityDecimalDigit(target);
    // 数量桁区切りセット
    setQuantityNumberFormat(target);
    // 値引小数桁数セット
    setDiscountDecimalDigit(target);
    // 値引桁区切りセット
    setDiscountNumberFormat(target);

    // 金額再計算
    setProductPrice(target);

    // 各合計値セット
    setTotalAmounts();
}

/**
 * 商品セレクトボックス変更処理
 * @param target
 */
window.changeProductCreateEdit = function (target) {
    let row = $(target).closest('tr');
    let name = row.find('.input-product-select option:selected').data('name');
    if (typeof name === 'undefined') {
        row.find('.input-product-code').val('');
        row.find('.input-product-name').val('');
        row.find('.input-product-unit-name-select').prop('selectedIndex', 0).change();
        row.find('.hidden-consumption-tax-rate').val('0');
        return;
    }

    // 選択されたコードの商品名をセット
    row.find('.input-product-name').val(name);

    // 単価履歴セット
    getSupplierUnitPriceHistory(target);

    let code = row.find('.input-product-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された商品のコードをセット
    row.find('.input-product-code').val(code);
    // 商品単位名セット
    setProductUnitName(target);
    // 税率セット
    setProductConsumptionTaxRate(target);
    // 税区分セット
    setProductTaxType(target);
    // 金額端数処理セット
    setAmountRoundingMethod(target);
    // 税額端数処理セット（得意先単位）
    setTaxRoundingMethod();
    // 単価履歴セット
    setUnitPriceHistory(target);
    // 単価小数桁数セット
    setUnitPriceDecimalDigit(target);
    // 単価桁区切りセット
    setUnitPriceNumberFormat(target);
    // 数量小数桁数セット
    setQuantityDecimalDigit(target);
    // 数量桁区切りセット
    setQuantityNumberFormat(target);
    // 値引小数桁数セット
    setDiscountDecimalDigit(target);
    // 値引桁区切りセット
    setDiscountNumberFormat(target);

    // 金額再計算
    setProductPrice(target);

    // 各合計値セット
    setTotalAmounts();
}

/**
 * 商品検索モーダル用 商品選択
 * @param target
 */
window.selectProductSearchProductModal = function (target) {
    let targetValue = $(target).data('code');

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    // 売上伝票の対象行の部門コードを変更
    let row_no = $('#modal_target_row').val();
    let target_row = $('#order_products_table tbody tr').eq(row_no);
    let targetOption = ".input-product-select option[data-code='" + parseInt(targetValue) + "']";

    target_row.find('.input-product-code').val(targetValue);

    if (target_row.find(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        target_row.find(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        target_row.find('.input-product-select').prop('selectedIndex', 0);
    }

    // 商品変更時の処理
    changeProductCreateEdit(target_row);

    // 商品検索モーダルを閉じる
    $('#search-product').modal('hide');

    // フォーカス移動ON
    flgChangeFocusSearchProduct = true;
}

/**
 * 商品コードセット処理
 * @param target
 */
window.setProductCode = function (target) {
    let row = $(target).closest('tr');
    let code = row.find('.input-product-select option:selected').data('code');

    // 選択されたコードの商品単位名をセット
    row.find('.input-product-code').val(code);
}

/**
 * 商品単位名セット処理
 * @param target
 */
window.setProductUnitName = function (target) {
    let row = $(target).closest('tr');
    let unitName = row.find('.input-product-select option:selected').data('unit-name');

    // 選択されたコードの商品単位名をセット
    row.find('.input-product-unit-name-select').val(unitName);
}

/**
 * 税率セット処理
 * @param target
 */
window.setProductConsumptionTaxRate = function (target) {
    let row = $(target).closest('tr');
    let taxTypeId = row.find('.input-product-select option:selected').data('tax-type-id');
    let reducedTaxFlag = row.find('.input-product-select option:selected').data('reduced-tax-flag');

    // 税率
    if (taxTypeId === window.Laravel.enums.tax_type.tax_exempt) {
        row.find('.input-consumption-tax-rate-select').val(0);
        row.find('.hidden-consumption-tax-rate').val(0);
    } else if (reducedTaxFlag && $('#reduced_tax_rate').val() > 0) {
        // 対象行の税率セレクトボックスを変更
        row.find('.input-consumption-tax-rate-select').val($('#reduced_tax_rate').val());
        row.find('.hidden-consumption-tax-rate').val($('#reduced_tax_rate').val());
    } else {
        // 対象行の税率セレクトボックスを変更
        row.find('.input-consumption-tax-rate-select').val($('#tax_rate').val());
        row.find('.hidden-consumption-tax-rate').val($('#tax_rate').val());
    }
}

/**
 * 税区分セット処理
 * @param target
 */
window.setProductTaxType = function (target) {
    let row = $(target).closest('tr');
    let taxRate = parseInt(row.find('.hidden-consumption-tax-rate').val());
    let taxTypeId = row.find('.input-product-select option:selected').data('tax-type-id');
    let reducedTaxFlag = row.find('.input-product-select option:selected').data('reduced-tax-flag');
    let taxTypeName = '[税抜]';
    let taxUnitName = '％';
    if (taxRate === 0) {
        taxTypeName = '　　';
        taxUnitName = '  ';
    }
    if (taxRate > 0) {
        taxTypeName = (taxTypeId === window.Laravel.enums.tax_type.in_tax) ? '[税込]' : '[税抜]';
    }
    // 税区分表記
    row.find('.label-tax-type-name').text(taxTypeName);
    row.find('.label-tax-unit-name').text(taxUnitName);
    row.find('.hidden-tax-type-id').val(taxTypeId);
    row.find('.hidden-reduced-tax-flag').val(reducedTaxFlag);
}

/**
 * 税計算区分セット処理
 */
window.setTaxCalcType = function () {
    // 選択された得意先の端数処理をセット
    $('.hidden-tax-calc-type-id')
        .val($('.input-supplier-select option:selected').data('tax-calc-type'));
}

/**
 * 税率端数処理セット処理
 */
window.setTaxRoundingMethod = function () {
    // 選択された得意先の端数処理をセット
    $('.hidden-tax-rounding-method-id')
        .val($('.input-supplier-select option:selected').data('tax-rounding-method'));
}

/**
 * 金額端数処理セット処理
 * @param target
 */
window.setAmountRoundingMethod = function (target) {
    let row = $(target).closest('tr');
    let rounding_method = row.find('.input-product-select option:selected').data('amount-rounding-method-id');

    // 選択された商品の金額端数処理をセット
    row.find('.hidden-amount-rounding-method-id').val(rounding_method);
}

/**
 * 単価履歴セット処理
 * @param target
 */
window.setUnitPriceHistory = function (target) {
    let row = $(target).closest('tr');
    let supplierId = $('.input-supplier-select option:selected').val();
    let productId = row.find('.input-product-select option:selected').val();
    let unitName = row.find('.input-product-unit-name-select').val();


    if (productId === '') {
        return;
    }

    let url = $('.hidden-api-get-unit-price-url').val();
    axios.get(url, {
        params: {
            supplier_id: supplierId,
            product_id: productId,
            unit_name: unitName,
        }
    }).then(response => {
        // 正常処理時
        row.find('.input-unit-price').val(response.data[0]);
        // 単価小数桁数セット
        setUnitPriceDecimalDigit(target);
        // 単価桁区切りセット
        setUnitPriceNumberFormat(target);
        // 数量小数桁数セット
        setQuantityDecimalDigit(target);
        // 数量桁区切りセット
        setQuantityNumberFormat(target);
        // 金額再計算
        setProductPrice(target);
        // 粗利再計算
        // setProductGross($(target));
        // 各合計値セット
        setTotalAmounts();
    }).catch(error => {
        // エラー時
        console.error(error);
    });
}

/**
 * 商品単位名変更時の処理
 * @param target
 */
window.changeUnitName = function (target) {
    // 単価履歴セット
    setUnitPriceHistory(target);
}

/**
 * 単価小数桁数セット処理
 * @param target
 */
window.setUnitPriceDecimalDigit = function (target) {
    let row = $(target).closest('tr');
    let digit = row.find('.input-product-select option:selected').data('unit-price-decimal-digit');
    let method = row.find('.input-product-select option:selected').data('amount-rounding-method-id');

    // 選択された商品の単価小数桁数をセット
    row.find('.hidden-unit-price-decimal-digit').val(digit);

    // 単価を桁数で反映
    let unitPrice = row.find('.input-unit-price').val().replace(/,/g, '');
    let calcUnitPrice = getFloorValueForDigit(parseFloat(unitPrice), digit, method);
    row.find('.input-unit-price').val(calcUnitPrice);
}

/**
 * 数量小数桁数セット処理
 * @param target
 */
window.setQuantityDecimalDigit = function (target) {
    let row = $(target).closest('tr');
    let digit = row.find('.input-product-select option:selected').data('quantity-decimal-digit');
    let method = row.find('.input-product-select option:selected').data('quantity-rounding-method-id');

    // 選択された商品の数量小数桁数をセット
    row.find('.hidden-quantity-decimal-digit').val(digit);

    // 数量を桁数で反映
    let quantity = row.find('.input-quantity').val().replace(/,/g, '');
    if (quantity === '') {
        // ブランクだったら、数量デフォルトセット
        quantity = '1';
    }

    let calcQuantity = getFloorValueForDigit(parseFloat(quantity), digit, method);
    row.find('.input-quantity').val(calcQuantity);
}

/**
 * 単価桁区切りセット処理
 * @param target
 */
window.setUnitPriceNumberFormat = function (target) {
    let row = $(target).closest('tr');
    let unitPrice = row.find('.input-unit-price').val().replace(/,/g, '');
    if (unitPrice === '') {
        return;
    }

    let digit = row.find('.input-product-select option:selected').data('unit-price-decimal-digit');
    let number = parseFloat(unitPrice).toLocaleString(undefined, {
        minimumFractionDigits: digit,
        maximumFractionDigits: digit
    });
    row.find('.input-unit-price').val(number);
}

/**
 * 数量桁区切りセット処理
 * @param target
 */
window.setQuantityNumberFormat = function (target) {
    let row = $(target).closest('tr');
    let quantity = row.find('.input-quantity').val().replace(/,/g, '');
    if (quantity === '') {
        return;
    }

    let digit = row.find('.input-product-select option:selected').data('quantity-decimal-digit');
    let number = parseFloat(quantity).toLocaleString(undefined, {
        minimumFractionDigits: digit,
        maximumFractionDigits: digit
    });
    row.find('.input-quantity').val(number);
}

/**
 * 値引小数桁数セット処理
 * @param target
 */
window.setDiscountDecimalDigit = function (target) {
    let row = $(target).closest('tr');
    let digit = row.find('.input-product-select option:selected').data('quantity-decimal-digit');
    let method = row.find('.input-product-select option:selected').data('quantity-rounding-method-id');

    // 選択された商品の数量小数桁数をセット
    row.find('.hidden-quantity-decimal-digit').val(digit);

    // 数量を桁数で反映
    let discount = row.find('.input-discount').val().replace(/,/g, '');
    if (discount === '') {
        // ブランクだったら、数量デフォルトセット
        discount = '0';
    }

    let calcQuantity = getFloorValueForDigit(parseFloat(discount), digit, method);
    row.find('.input-discount').val(calcQuantity);
}

/**
 * 値引桁区切りセット処理
 * @param target
 */
window.setDiscountNumberFormat = function (target) {
    let row = $(target).closest('tr');
    let discount = row.find('.input-discount').val().replace(/,/g, '');
    if (discount === '') {
        return;
    }

    let digit = 0;//row.find('.input-product-select option:selected').data('discount-decimal-digit');
    let number = parseFloat(discount).toLocaleString(undefined, {
        minimumFractionDigits: digit,
        maximumFractionDigits: digit
    });
    row.find('.input-discount').val(number);
}

/**
 * 値引桁区切りセット処理
 */
window.setOrderDiscountNumberFormat = function () {
    let discount = $('.input-order-discount').val().replace(/,/g, '');
    if (discount === '') {
        return;
    }

    let digit = 0;
    let number = parseFloat(discount).toLocaleString(undefined, {
        minimumFractionDigits: digit,
        maximumFractionDigits: digit
    });
    $('.input-order-discount').val(number);
}

/**
 * 桁数で切り捨てされた値を取得
 *
 * @param targetValue
 * @param digit
 * @param method
 * @returns {number|*}
 */
window.getFloorValueForDigit = function (targetValue, digit, method) {
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
 * 商品金額セット処理
 *
 * @param target
 */
window.setProductPrice = function (target) {
    let row = $(target).closest('tr');

    // 金額セット（桁区切り有）
    let quantity = row.find('.input-quantity').val().replace(/,/g, '');
    let unitPrice = row.find('.input-unit-price').val().replace(/,/g, '');
    let discount = row.find('.input-discount').val().replace(/,/g, '');
    let sub_total_rounding_method = $('.input-supplier-select option:selected').data('tax-rounding-method');
    let subTotal = calcAmountRounding(sub_total_rounding_method, ((unitPrice * quantity) - discount));

    // 小計セット(税抜き)
    row.find('.input-product-sub-total').val(subTotal.toLocaleString());
}

/**
 * 粗利金額セット処理
 *
 * @param target
 */
window.setProductGross = function (target) {
    let row = $(target).closest('tr');

    // 金額セット（桁区切り有）
    let quantity = row.find('.input-quantity').val().replace(/,/g, '');
    let unitPrice = row.find('.input-unit-price').val().replace(/,/g, '');
    let purchaseUnitPrice = row.find('.input-unit-price-purchase').val().replace(/,/g, '');
    // 粗利セット
    let sub_total_rounding_method = $('.input-supplier-select option:selected').data('tax-rounding-method');
    let subgross = 0;
    if (purchaseUnitPrice > 0) {
        subgross = calcAmountRounding(sub_total_rounding_method, (unitPrice * quantity - purchaseUnitPrice * quantity));
    } else {
        subgross = calcAmountRounding(sub_total_rounding_method, (unitPrice * quantity));
    }
    row.find('.input-product-sub-gross').val(subgross.toLocaleString());
}

/**
 * 各合計値セット
 *   .hidden-tax-calc-type-id  1:伝票毎 2:請求毎 3;明細毎
 *   .hidden-tax-type-id       1:外税   2:内税
 */
window.setTotalAmounts = function () {
    // 合計情報
    let total = {
        'taxTotal': 0,                      // 外税合計
        'inTaxTotal': 0,                    // 税抜合計
        'consumptionTotal': 0,              // 通常税率 - 外税対象税抜額
        'consumptionTaxTotal': 0,           // 通常税率 - 外税額
        'consumptionInTotal': 0,            // 通常税率 - 内税対象税抜額
        'consumptionInTaxTotal': 0,         // 通常税率 - 内税額
        'reducedTotal': 0,                  // 軽減税率 - 外税対象税抜額
        'reducedTaxTotal': 0,               // 軽減税率 - 外税額
        'reducedInTotal': 0,                // 軽減税率 - 内税対象税抜額
        'reducedInTaxTotal': 0,             // 軽減税率 - 内税額
        'notaxTotal': 0,                    // 非課税対象税抜合計
    };
    // 値引情報
    let individualGroupDiscount = {
        'consumptionTotalDiscount': 0,
        'consumptionInTotalDiscount': 0,
        'reducedTotalDiscount': 0,
        'reducedInTotalDiscount': 0,
        'notaxTotalDiscount': 0,
    }

    // 明細行情報
    let row_data = [];

    // 税計算区分(1:伝票毎 2:請求毎 3:明細毎) ※3は使用しない
    let taxCalcTypeId = parseInt($('.input-supplier-select option:selected').data('tax-calc-type'));
    let groupTotal = [];
    let order_discount = parseInt($('.input-order-discount').val().replace(/,/g, ''));
    if (isNaN(order_discount)) {
        order_discount = 0;
    }

    $('#order_products_table tbody tr').each(function () {
        // 数量
        let quantity = parseFloat($(this).find('.input-quantity').val().replace(/,/g, ''));
        if (isNaN(quantity)) {
            quantity = 0;
        }
        // 単価
        let price = parseFloat($(this).find('.input-unit-price').val().replace(/,/g, ''));
        if (isNaN(price)) {
            price = 0;
        }
        // 値引額
        let discount = parseFloat($(this).find('.input-discount').val().replace(/,/g, ''));
        if (isNaN(discount)) {
            discount = 0;
        }

        if (quantity === 0 || price === 0) {
            return true;
        }

        // 小計
        let sub_total_rounding_method = $('.input-supplier-select option:selected').data('tax-rounding-method');
        let subTotal1 = calcAmountRounding(sub_total_rounding_method, ((quantity * price) - discount));
        // 税計算準備
        let taxTypeId = parseInt($(this).find('.hidden-tax-type-id').val());
        let taxRate = parseInt($(this).find('.input-consumption-tax-rate-select').val());
        let reducedTaxFlag = parseInt($(this).find('.hidden-reduced-tax-flag').val());
        let taxRoundingmethod = parseInt($(this).find('.hidden-tax-rounding-method-id').val());

        // 明細単位の税計算
        let row_total = calcTax(taxCalcTypeId, taxTypeId, taxRate, reducedTaxFlag, taxRoundingmethod, subTotal1);
        if (reducedTaxFlag === window.Laravel.enums.reduced_tax_flag_type.reduced) {
            if (taxTypeId === window.Laravel.enums.tax_type.out_tax) {
                // 外税
                $(this).find('.hidden-tax').val(row_total['reducedTax']);
            }
            if (taxTypeId === window.Laravel.enums.tax_type.in_tax) {
                // 内税
                $(this).find('.hidden-tax').val(row_total['reducedInTax']);
            }
        } else {
            if (taxTypeId === window.Laravel.enums.tax_type.out_tax) {
                // 外税
                $(this).find('.hidden-tax').val(row_total['consumptionTax']);
            }
            if (taxTypeId === window.Laravel.enums.tax_type.in_tax) {
                // 内税
                $(this).find('.hidden-tax').val(row_total['consumptionInTax']);
            }
        }
        if (taxCalcTypeId === window.Laravel.enums.tax_calc_type.order) {
            // 伝票単位での計算用に各種合計金額を保持しておく
            let key = taxTypeId + '-' + taxRate + '-' + reducedTaxFlag + '-' + taxRoundingmethod
            if (groupTotal[key] !== undefined) {
                groupTotal[key] += subTotal1
            } else {
                groupTotal[key] = subTotal1;
            }
            return true;
        }

        total['taxTotal'] += row_total['tax'];
        total['inTaxTotal'] += row_total['inTax'];
        total['consumptionTotal'] += row_total['consumption'];
        total['consumptionInTotal'] += row_total['consumptionIn'];
        total['reducedTotal'] += row_total['reduced'];
        total['reducedInTotal'] += row_total['reducedIn'];
        total['notaxTotal'] += row_total['notax'];

        let row = {
            taxTypeId: taxTypeId,
            taxRate: taxRate,
            reducedTaxFlag: reducedTaxFlag,
            taxRoundingmethod: taxRoundingmethod,
            consumptionTotal: row_total.consumption,
            consumptionInTotal: row_total.consumptionIn,
            reducedTotal: row_total.reduced,
            reducedInTotal: row_total.reducedIn,
            notaxTotal: row_total.notax
        };
        row_data.push(row);
    });

    // ☆以後、全行サーチ後の処理
    if (!isNaN(taxCalcTypeId) && taxCalcTypeId === window.Laravel.enums.tax_calc_type.order) {
        // 伝票単位の税計算
        let row_total = calcOrderTax(total, taxCalcTypeId, groupTotal);
        if (row_total['taxTotal'] !== undefined) {
            total = row_total;
        }
    }

    let subTotal = total['consumptionTotal'] + total['consumptionInTotal'] + total['reducedTotal'] + total['reducedInTotal'] + total['notaxTotal'];
    if (isNaN(subTotal)) {
        subTotal = 0;
    }
    $('.text-sub-total-out-discount').text('¥' + subTotal.toLocaleString());
    $('.text-order-discount').text('¥' + order_discount.toLocaleString());
    $('.text-sub-total').text('¥' + (subTotal-order_discount).toLocaleString());

    // 値引を決定
    calcIndividualGroupDiscount(total, individualGroupDiscount, subTotal, order_discount);
    // 値引を差し引く
    calcIndividualGroupTotalAfterDiscount(total, individualGroupDiscount);
    // 税金の計算
    let method = $('.input-supplier-select option:selected').data('tax-rounding-method');
    row_data.forEach((row, index) => {
        let price = 0;
        let diff_discount = 0;
        if (row.reducedTaxFlag === window.Laravel.enums.reduced_tax_flag_type.reduced) {
            if (row.taxTypeId === window.Laravel.enums.tax_type.out_tax) {
                // 外税
                price = row.reducedTotal;
                // 値引案分取得
                diff_discount = calcChunkDiscount(order_discount, price, subTotal);
                // 値引案分を差し引いた「消費税 軽減税率」合計
                total['reducedTaxTotal'] += calcTaxRounding(row.taxRate, method, (price - diff_discount));
            }
            if (row.taxTypeId === window.Laravel.enums.tax_type.in_tax) {
                // 内税
                price = row.reducedInTotal;
                // 値引案分取得
                diff_discount = calcChunkDiscount(order_discount, price, subTotal);
                // 値引案分を差し引いた「消費税 軽減税率」合計
                total['reducedInTaxTotal'] += calcTaxIn(row.taxRate, method, (price - diff_discount));
            }
        } else {
            if (row.taxTypeId === window.Laravel.enums.tax_type.out_tax) {
                // 外税
                price = row.consumptionTotal;
                // 値引案分取得
                diff_discount = calcChunkDiscount(order_discount, price, subTotal);
                // 値引案分を差し引いた「消費税 通常税率」合計
                total['consumptionTaxTotal'] += calcTaxRounding(row.taxRate, method, (price - diff_discount));
            }
            if (row.taxTypeId === window.Laravel.enums.tax_type.in_tax) {
                // 内税
                price = row.consumptionInTotal;
                // 値引案分取得
                diff_discount = calcChunkDiscount(order_discount, price, subTotal);
                // 値引案分を差し引いた「消費税 通常税率」合計
                total['consumptionInTaxTotal'] += calcTaxIn(row.taxRate, method, (price - diff_discount));
            }
        }
    });

    // 税率マスターから軽減税率が有効か
    let reduced_valid = ($('#reduced_tax_rate').val() > 0);

    let consumption = total['consumptionTotal'] + total['consumptionInTotal'];
    let reduced = total['reducedTotal'] + total['reducedInTotal'];
    if (reduced_valid) {
        $('.text-consumption-total').text('¥' + (consumption).toLocaleString());
        $('.text-reduced-total').text('¥' + (reduced).toLocaleString());
    } else {
        $('.text-consumption-total').text('¥' + (consumption + reduced).toLocaleString());
    }
    $('.text-notax-total').text('¥' + total['notaxTotal'].toLocaleString());

    if (reduced_valid) {
        $('.text-consumption-tax').text('¥' + total['consumptionTaxTotal'].toLocaleString());
        $('.text-reduced-tax').text('¥' + total['reducedTaxTotal'].toLocaleString());
    } else {
        $('.text-consumption-tax').text('¥' + (total['consumptionTaxTotal'] + total['reducedTaxTotal']).toLocaleString());
    }

    if (taxCalcTypeId === window.Laravel.enums.tax_calc_type.billing) {
        // 請求毎の締処理の場合「(別途計算)」と表記する
        $('.text-consumption-tax').text('(請求時計算)');
        $('.text-reduced-tax').text('(請求時計算)');
    }
    if (taxCalcTypeId === window.Laravel.enums.tax_calc_type.none) {
        // 請求毎の締処理の場合「(別途計算)」と表記する
        $('.text-consumption-tax').text('(無処理)');
        $('.text-reduced-tax').text('(無処理)');
    }

    // 合計
    $('.text-inctax-total').text('¥' + (subTotal - order_discount).toLocaleString());
    if (taxCalcTypeId === window.Laravel.enums.tax_calc_type.order || taxCalcTypeId === window.Laravel.enums.tax_calc_type.detail) {
        $('.text-inctax-total').text(
            '¥' + (subTotal + total['consumptionTaxTotal'] + total['reducedTaxTotal'] - order_discount).toLocaleString());
    }

    // 各種税額のセット
    $('.hidden-purchase-total').val(subTotal);
    if (reduced_valid) {
        $('.hidden-purchase-total-normal-out').val(total['consumptionTotal']);
        $('.hidden-purchase-total-reduced-out').val(total['reducedTotal']);
        $('.hidden-purchase-total-normal-in').val(total['consumptionInTotal']);
        $('.hidden-purchase-total-reduced-in').val(total['reducedInTotal']);
        $('.hidden-purchase-total-free').val(total['notaxTotal']);
        $('.hidden-purchase-tax-normal-out').val(total['consumptionTaxTotal']);
        $('.hidden-purchase-tax-reduced-out').val(total['reducedTaxTotal']);
        $('.hidden-purchase-tax-normal-in').val(total['consumptionInTaxTotal']);
        $('.hidden-purchase-tax-reduced-in').val(total['reducedInTaxTotal']);
    } else {
        $('.hidden-purchase-total-normal-out').val(total['consumptionTotal'] + total['reducedTotal']);
        $('.hidden-purchase-total-normal-in').val(total['consumptionInTotal'] + total['reducedInTotal']);
        $('.hidden-purchase-total-free').val(total['notaxTotal']);
        $('.hidden-purchase-tax-normal-out').val(total['consumptionTaxTotal'] + total['reducedTaxTotal']);
        $('.hidden-purchase-tax-normal-in').val(total['consumptionInTaxTotal'] + total['reducedInTaxTotal']);

        $('.hidden-purchase-total-reduced-out').val(0);
        $('.hidden-purchase-total-reduced-in').val(0);
        $('.hidden-purchase-tax-reduced-out').val(0);
        $('.hidden-purchase-tax-reduced-in').val(0);
    }

    if (reduced_valid) {
        $('.text-purchase-total-normal-out').text('¥' + total['consumptionTotal'].toLocaleString());
        $('.text-purchase-total-reduced-out').text('¥' + total['reducedTotal'].toLocaleString());
        $('.text-purchase-total-normal-in').text('(¥' + total['consumptionInTotal'].toLocaleString() + ')');
        $('.text-purchase-total-reduced-in').text('(¥' + total['reducedInTotal'].toLocaleString() + ')');
        $('.text-purchase-total-free').text('¥' + total['notaxTotal'].toLocaleString());
        $('.text-purchase-tax-normal-out').text('¥' + total['consumptionTaxTotal'].toLocaleString());
        $('.text-purchase-tax-reduced-out').text('¥' + total['reducedTaxTotal'].toLocaleString());
        $('.text-purchase-tax-normal-in').text('(¥' + total['consumptionInTaxTotal'].toLocaleString() + ')');
        $('.text-purchase-tax-reduced-in').text('(¥' + total['reducedInTaxTotal'].toLocaleString() + ')');
    } else {
        $('.text-purchase-total-normal-out').text('¥' + (total['consumptionTotal'] + total['reducedTotal']).toLocaleString());
        $('.text-purchase-total-normal-in').text('(¥' + (total['consumptionInTotal'] + total['reducedInTotal']).toLocaleString() + ')');
        $('.text-purchase-total-free').text('¥' + total['notaxTotal'].toLocaleString());
        $('.text-purchase-tax-normal-out').text('¥' + (total['consumptionTaxTotal'] + total['reducedTaxTotal']).toLocaleString());
        $('.text-purchase-tax-normal-in').text('(¥' + (total['consumptionInTaxTotal'] + total['reducedInTaxTotal']).toLocaleString() + ')');

        $('.text-purchase-total-reduced-out').text('¥0');
        $('.text-purchase-total-reduced-in').text('(¥0)');
        $('.text-purchase-tax-reduced-out').text('¥0');
        $('.text-purchase-tax-reduced-in').text('(¥0)');
    }
}


/**
 * 指定の伝票番号へ遷移
 * @param order_number
 */
window.changeOrder = function (order_number) {
    if (order_number === undefined) {
        return;
    }
    let sub_dir = $('.hidden-sub-dir').val();
    // 指定の伝票番号へ遷移
    window.location.href = sub_dir + 'sale/orders/' + order_number + '/edit';
}


/**
 * 登録更新処理
 */
window.store = function () {
    // モーダル閉じる
    $('#confirm-store').modal('hide');

    let order_discount = $('.input-order-discount').val().replace(/,/g, '');
    $('.input-order-discount').val(order_discount);

    $('#order_products_table tbody tr').each(function () {
        let closest_td = $(this).children('td');

        //商品コード未入力行をdisabled
        if (!$(this).find('.input-product-code').val()) {
            closest_td.find('*').prop('disabled', 'true');
            $(this).find('.input-sort').prop('disabled', 'true');
            $(this).find('.input-checked-sales-confirm').prop('disabled', 'true');
        }

        //数量(桁区切り無し)
        let quantity = $(this).find('.input-quantity').val().replace(/,/g, '');
        $(this).find('.input-quantity').val(quantity);
        let unitPrice = $(this).find('.input-unit-price').val().replace(/,/g, '');
        $(this).find('.input-unit-price').val(unitPrice);
        let discount = $(this).find('.input-discount').val().replace(/,/g, '');
        $(this).find('.input-discount').val(discount);
    });

    // ボタン非活性
    disableButtons();
    // ローディング切替
    changeStoreLoading();

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

    $('#order_products_table tbody tr').each(function () {
        let closest_td = $(this).children('td');

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

    // submit前の書換処理
    $('#copy_number').val($('#order_number').val());
    $('#editForm').attr('action', route);
    $('input:hidden[name="_method"]').val("POST");

    // submit処理
    $('#editForm').submit();
}
