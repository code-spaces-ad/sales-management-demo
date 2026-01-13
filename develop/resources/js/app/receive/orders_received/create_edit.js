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
 * ロードイベントに追加
 */
window.addEventListener('load', function () {
    // 伝票日付にフォーカス
    $('#order_date').focus();
    // 得意先セレクトボックスチェンジイベント発火
    $('.input-customer-select').change();
    // 納品先セレクトボックスチェンジイベント発火
    $('.input-branch-select').change();
    // 担当者セレクトボックスチェンジイベント発火
    $('.input-employee-select').change();

    // 倉庫セレクトボックスチェンジイベント発火
    $('.input-warehouse-name-select').change();

    $('#order_products_table tbody tr').each(function () {
        // 商品コードセット
        setProductCode($(this));

        //納品書印刷チェックのdisabled判定
        if ($(this).closest('tr').find('.input-delivery-date').val()) {
            $(this).closest('tr').find('.input-delivery-print').prop('disabled', false);
        }
    });
    $('#return').removeClass('back_inactive');
    $('#return').addClass('back_active');

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
    $('.input-quantity').focus(function () {
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
    $('.input-quantity').keydown(function (event) {
        // 「type='number'」は「e」が入力可能なので除外
        if (event.key === 'e') {
            return false;
        }
    });

    /**
     * 数量 ブラーイベント
     */
    $("#order_products_table tbody").on('blur', '.input-quantity', function () {
        let quantityStr = $(this).val();//.replace(/[^\.0-9]/g, '');   // ※マイナス値不可
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
    });

    /**
     * autocomplete
     */
    $('#recipient_name').autocomplete({
        source: function (req, resp) {
            let branch_id = $('.input-branch-select').val();

            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: $('.hidden-autocomplete-list-recipient-name').val(),
                type: 'POST',
                cache: false,
                dataType: 'json',
                data: {
                    search: req.term,
                    branch: branch_id
                },
                success: function (o) {
                    resp(o.data);
                    // 更新されたcsrfトークンをセット
                    $('meta[name="csrf-token"]').attr('content', o.csrf_token);
                    $('input[name="_token"]').attr('value', o.csrf_token);
                },
                error: function (xhr, ts, err) {
                    resp(['']);
                }
            });
        },
        select: function (event, ui) {
            let branch_id = $('.input-branch-select').val();

            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: $('.hidden-autocomplete-list-recipient-name-kana').val(),
                type: 'POST',
                cache: false,
                dataType: 'json',
                data: {
                    search: ui.item.value,
                    branch: branch_id
                },
                success: function (o) {
                    $('input[name="recipient_name_kana"]').val(o.data);
                    // 更新されたcsrfトークンをセット
                    $('meta[name="csrf-token"]').attr('content', o.csrf_token);
                    $('input[name="_token"]').attr('value', o.csrf_token);
                },
                error: function (xhr, ts, err) {
                    $('input[name="recipient_name_kana"]').val('');
                }
            });
        }
    });

    //autoKana
    $.fn.autoKana('input[name="recipient_name"]', 'input[name="recipient_name_kana"]', {katakana: false});

    //.pc-no-display と同じ画面サイズで比較
    if (!window.matchMedia('(min-width:768px)').matches) {
        //マイナス入力トグルのチェックON/OFF
        changeToggleSwitchIfMinus();
    }

    //数量桁区切りセット
    $('.input-quantity').map(function () {
        let quantity = parseFloat($(this).closest('tr').find('.input-quantity').val());
        $(this).closest('tr').find('.input-quantity').val('');
        if (!isNaN(quantity)) {
            quantity = quantity.toLocaleString(undefined, {
                style: "decimal",
                useGrouping: true,
                minimumFractionDigits: 0,
                maximumFractionDigits: 4,
            })
            $(this).closest('tr').find('.input-quantity').val(quantity);
        }
    });

    //PDFボタンのアクティブ切り替え
    changeShowPDF();

    //納品書印刷フラグ
    $('.input-delivery-print').on('click', function () {
        //納品書ボタンdisabled
        $('#show_pdf').prop('disabled', true);
        $('#confirm_show_pdf').prop('disabled', true);

        //PDFボタンのアクティブ切り替え
        changeShowPDF();
    })
});

/**
 * PDFボタンのアクティブ切り替え
 *
 * @returns void
 */
window.changeShowPDF = function () {
    $('#order_products_table tbody tr').each(function (key) {
        let row = $(this).closest('tr').find('.input-delivery-print');
        let hidden_row = $('#showPdfForm').find('input[name="detail[' + key + '][delivery_print]"]');

        //value「0」セット
        row.val(0);
        hidden_row.val(0);

        if (row.prop('checked')) {
            //納品書ボタンenabled
            $('#show_pdf').prop('disabled', false);
            $('#confirm_show_pdf').prop('disabled', false);
            //value「1」セット
            row.val(1);
            hidden_row.val(1);
        }
    })
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
 * フォーム読み込み時に処理
 */
window.onload = function () {
    // 支所セレクトボックスフィルタリング
    filterBranch();
}

/**
 * クリア処理
 */
window.clearInput = function () {
    $('.invalid-feedback').remove();                // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    $('.input-order-date').val($('#default_order_date').val());

    $('.input-customer-select').prop('selectedIndex', 0).change();
    changeCustomerCreateEdit();

    $('.input-constr-site-select').prop('selectedIndex', 0).change();

    $('.input-employee-select').prop('selectedIndex', 0).change();
    changeEmployee();
    $('.input-order-status-select').prop('selectedIndex', 0).change();

    $('.input-product-select').prop('selectedIndex', 0).change();
    $('.input-product-name').val('');
    $('.input-quantity').val('');
    $('.input-unit-name-select').prop('selectedIndex', 0).change();
    $('.input-unit-price').val('');
    $('.input-tax-rate-select').prop('selectedIndex', 0).change();
    $('.input-product-sub-total').val('0');
    $('.input-delivery-date').val('');
    $('.input-note').val('');
    $('.input-sales-confirm').val('');

    $('.hidden-rounding-method-id').val('');
}

/**
 * 対象の商品行クリア
 */
window.clearProduct = function(target) {
    let row = $(target).closest('tr');

    row.find('.invalid-feedback').remove();                // エラーメッセージクリア
    row.find('.is-invalid').removeClass('is-invalid');     // エラー枠クリア

    //select
    row.find('.clear-select').prop('selectedIndex', 0).change();
    //value
    row.find('.clear-value').val('');
    //check
    row.find('.clear-check').prop('checked', false);
    //disabled
    row.find('.change-disabled').prop('disabled', true);

    //納品書ボタンdisabled
    $('#show_pdf').prop('disabled', true);
    $('#confirm_show_pdf').prop('disabled', true);

    $('#order_products_table tbody tr').each(function () {
        if ($(this).closest('tr').find('.input-delivery-print').prop('checked')) {
            //納品書ボタンenabled
            $('#show_pdf').prop('disabled', false);
            $('#confirm_show_pdf').prop('disabled', false);
        }
    })
}

/**
 * 得意先コード変更時の処理（得意先）
 * @param target
 */
window.changeCustomerCodeCreateEdit = function(target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-customer-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-customer-select').prop('selectedIndex', 0).change();
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');

    // 得意先セレクトボックス変更処理
    changeCustomerCreateEdit();
}

/**
 * 得意先セレクトボックス変更処理
 */
window.changeCustomerCreateEdit = function () {
    let code = $('.input-customer-select option:selected').data('code');

    function zeroPad(num, places) {
        let zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    let zero_fill_code = code;
    if (code) {
        zero_fill_code = zeroPad(code, 8);
    }
    // 選択された得意先名のコードをセット
    $('.input-customer-code').val(zero_fill_code);

    $('.input-branch-select').prop('disabled', false);

    if (!code) {
        $('.input-branch-select').prop('disabled', true);
        $('.input-branch-select').prop('selectedIndex', 0).change();
    }

    // 支所セレクトボックスフィルタリング
    filterBranch();
}

/**
 * 支所セレクトボックス変更処理
 */
window.changeBranchCreateEdit = function () {
    let name = $('.input-branch-select option:selected').data('name');

    $('.input-recipient-name').prop('disabled', false);
    $('.input-recipient-name-kana').prop('disabled', false);

    if (!name) {
        $('.input-recipient-name').prop('disabled', true);
        $('.input-recipient-name').val('');
        $('.input-recipient-name-kana').prop('disabled', true);
        $('.input-recipient-name-kana').val('');
    }

    // 納品先セレクトボックスフィルタリング
    filterRecipient();
}

window.makeWarehouseNameRequired = function(target) {
    $(target).closest('tr').find('.input-warehouse-name-select').prop('disabled', false);
    if (!$(target).val()) {
        $(target).closest('tr').find('.input-warehouse-name-select').prop('disabled', true);
        $(target).closest('tr').find('.input-warehouse-name-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 得意先検索モーダル用 得意先選択
 * @param target
 */
window.selectCustomerDeliverySearchCustomerModal = function(target) {
    let targetValue = $(target).data('customer-code');
    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    // フォーカス移動ON
    flgChangeFocusSearchCustomer = true;
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

/**
 * モーダル変更後の対象行を保存
 */
window.modalTargetRow = function (row_no) {
    $('#modal_target_row').val(row_no);
}

/**
 * 支所セレクトボックスフィルタリング処理
 */
window.filterBranch = function () {
    // 全体を一旦クリア
    $(".hidden-branch").unwrap();
    $(".input-branch-select option").removeClass('hidden-branch');

    let targetCustomerId = $('.input-customer-select option:selected').val();
    if (targetCustomerId !== '') {
        $(".input-branch-select option").each(function () {
            if ($(this).data('customer-id') === undefined) {
                return true;
            }
            if ($(this).data('customer-id') !== parseInt(targetCustomerId)) {
                // 得意先IDが不一致は非表示セット
                $(this).addClass('hidden-branch');
                $(this).wrap("<span class='d-none'></span>");
            }
        });
    }

    let selectIndex = $('.input-branch-select').prop('selectedIndex');
    // 選択状態でない、または選択状態が非表示の場合
    if (selectIndex === -1 || $('.input-branch-select :selected').hasClass('hidden-branch')) {
        // 未選択状態に変更する
        $(".input-branch-select option:selected").prop("selected", false);
        $('.input-branch-select').prop("selectedIndex", 0);

        $(".input-recipient-select option:selected").prop("selected", false);
        $('.input-recipient-select').prop('selectedIndex', 0).change();
    }
    filterRecipient();
}

window.filterRecipient = function () {
    // 全体を一旦クリア
    $(".hidden-recipient").unwrap();
    $(".input-recipient-select option").removeClass('hidden-recipient');

    let targetCustomerId = $('.input-customer-select option:selected').val();
    let targetBranchId = $('.input-branch-select option:selected').val();
    if (targetCustomerId !== '' || targetBranchId !== '') {
        $(".input-recipient-select option").each(function () {
            if ($(this).data('branch-id') === undefined) {
                return true;
            }

            if (targetCustomerId !== '' && $(this).data('customer-id') !== parseInt(targetCustomerId)) {
                // 支所IDが不一致は非表示セット
                $(this).addClass('hidden-recipient');
                $(this).wrap("<span class='d-none'></span>");
                return true;
            }

            if (targetBranchId !== '' && $(this).data('branch-id') !== parseInt(targetBranchId)) {
                // 支所IDが不一致は非表示セット
                $(this).addClass('hidden-recipient');
                $(this).wrap("<span class='d-none'></span>");
            }
        });
    }

    let selectIndex = $('.input-recipient-select').prop('selectedIndex');
    // 選択状態でない、または選択状態が非表示の場合
    if (selectIndex === -1 || $('.input-recipient-select :selected').hasClass('hidden-recipient')) {
        // 未選択状態に変更する
        $(".input-recipient-select option:selected").prop("selected", false);
        $('.input-recipient-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 倉庫セレクトボックス変更処理
 */
window.changeWarehouseName = function(target) {
    let code = $('.input-warehouse-name-select option:selected').data('code');
    // 選択された倉庫名（倉庫名）のコードをセット
    $('.input-warehouse-name-select-code').val(code);

    $(target).closest('tr').find('.input-sales-confirm').prop('disabled', false);

    if (!$(target).val()) {
        $(target).closest('tr').find('.input-sales-confirm').prop('disabled', true);
        $(target).closest('tr').find('.input-sales-confirm').prop('checked', false);
    }
}


/**
 * 商品コード変更時の処理
 * @param target
 */
window.changeProductCodeCreateEdit = function(target) {
    let targetValue = $(target).closest('tr').find('.input-product-code').val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-product-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(target).closest('tr').find(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(target).closest('tr').find(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        // $(target).val('');  // コード枠はクリア
        $(target).closest('tr').find('.input-product-select').prop('selectedIndex', 0).change();
    }
    // 選択されたコードの商品名をセット
    let name = $(target).closest('tr').find('.input-product-select option:selected').data('name');
    $(target).closest('tr').find('.input-product-name').val(name);

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 商品セレクトボックス変更処理
 * @param target
 */
window.changeProductCreateEdit = function(target) {
    let name = $(target).closest('tr').find('.input-product-select option:selected').data('name');
    if (typeof name === 'undefined') {
        // 選択されたコードのコードをクリア
        $(target).closest('tr').find('.input-product-code').val('');
        // 選択されたコードの商品名をクリア
        $(target).closest('tr').find('.input-product-name').val('');
        return;
    }

    // 選択されたコードの商品名をセット
    $(target).closest('tr').find('.input-product-name').val(name);

    let code = $(target).closest('tr').find('.input-product-select option:selected').data('code');

    function zeroPad(num, places) {
        let zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された商品のコードをセット
    $(target).closest('tr').find('.input-product-code').val(code);
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

    // 受注伝票の対象行の部門コードを変更
    let row_no = $('#modal_target_row').val();
    let target_row = $('#order_products_table tbody tr').eq(row_no);
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
}

/**
 * 商品コードセット処理
 * @param target
 */
window.setProductCode = function(target) {
    let row = $(target).closest('tr');
    let code = row.find('.input-product-select option:selected').data('code');

    // 選択された行の商品コードをセット
    row.find('.input-product-code').val(code);
}

/**
 * 登録更新処理
 */
window.store = function () {
    // モーダル閉じる
    $('#confirm-store').modal('hide');

    // visibilitychangeイベントを無効
    $(window).off('visibilitychange');

    $('#order_products_table tbody tr').each(function () {
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

    // submit処理
    $('#editForm').submit();
}

/**
 * 登録更新処理
 */
window.storeAndShowPdf = function () {
    // モーダル閉じる
    $('#confirm-store-showpdf').modal('hide');

    // visibilitychangeイベントを無効
    $(window).off('visibilitychange');

    $('#order_products_table tbody tr').each(function () {
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

    // submit処理
    $('#editForm').attr('action', $('#next_url2').val());
    $('#editForm').submit();
}

/**
 * 削除処理
 */
window.destory = function () {
    // モーダル閉じる
    $('#confirm-delete').modal('hide');

    // visibilitychangeイベントを無効
    $(window).off('visibilitychange');

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

    // visibilitychangeイベントを無効
    $(window).off('visibilitychange');

    $('#order_products_table tbody tr').each(function () {
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

    // submit前の書換処理
    $('#copy_number').val($('#order_number').val());
    $('#editForm').attr('action', route);
    $('input:hidden[name="_method"]').val("POST");

    // submit処理
    $('#editForm').submit();
}
