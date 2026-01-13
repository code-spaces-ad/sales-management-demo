/**
 * 得意先コード変更時の処理
 */
window.changeCustomerCode = function (target) {
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
        $(target).val('');  // コード枠はクリア
        $('.input-customer-select').prop('selectedIndex', 0).change();
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 得意先セレクトボックス変更処理
 */
window.changeCustomer = function () {
    let code = $('.input-customer-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された得意先名のコードをセット
    $('.input-customer-code').val(code);

    $('.input-branch-select').prop('disabled', false);

    if (!code) {
        $('.input-branch-select').prop('disabled', true);
    }

    // 支所セレクトボックスフィルタリング
    filterBranch();
}

/**
 * 支所セレクトボックス変更処理
 */
window.changeBranch = function () {
    let name = $('.input-branch-select option:selected').data('name');

    $('.input-recipient-select').prop('disabled', false);

    if (!name) {
        $('.input-recipient-select').prop('disabled', true);
    }

    // 納品先セレクトボックスフィルタリング
    filterRecipient();
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
        $('.input-branch-select').prop("selectedIndex", 0).change();

        $(".input-recipient-select option:selected").prop("selected", false);
        $('.input-recipient-select').prop('selectedIndex', 0).change();
    }
    filterRecipient();
}

/**
 * 納品先セレクトボックスフィルタリング
 */
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
 * 商品コード変更時の処理
 */
window.changeProductCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-product-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    }

    //select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 商品セレクトボックス変更処理
 */
window.changeProduct = function () {
    let code = $('.input-product-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された商品のコードをセット
    $('.input-product-code').val(code);
}

/**
 * 仕入先コード変更時の処理
 */
window.changeSupplierCode = function (target) {
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
}

/**
 * 仕入先セレクトボックス変更処理
 */
window.changeSupplier = function () {
    let code = $('.input-supplier-select option:selected').data('code');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 8)
    }
    // 選択された仕入先のコードをセット
    $('.input-supplier-code').val(code);
}

/**
 * 担当者コード変更時の処理
 */
window.changeEmployeeCode = function (target) {
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
 * 倉庫コード変更時の処理
 */
window.changeWarehouseCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-warehouse-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    }
}

/**
 * 倉庫セレクトボックス変更処理
 */
window.changeWarehouse = function () {
    let code = $('.input-warehouse-select option:selected').data('code');
    // 選択された倉庫のコードをセット
    $('.input-warehouse-code').val(code);
}

/**
 * 集計グループコード変更時の処理
 */
window.changeSummaryGroupCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-summary-group-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-summary-group-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 集計グループセレクトボックス変更処理
 */
window.changeSummaryGroup = function () {
    let code = $('.input-summary-group-select option:selected').data('code');
    // 選択された集計グループのコードをセット
    $('.input-summary-group-code').val(code);
}

/**
 * 経理コード変更時の処理
 */
window.changeAccountingCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-accounting-code-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $('.input-accounting-code-select').prop('selectedIndex', 0).change();
    }
}

/**
 * 経理コードセレクトボックス変更処理
 */
window.changeAccountingCodeSelect = function () {
    let code = $('.input-accounting-code-select option:selected').data('code');
    // 選択された経理のコードをセット
    $('.input-accounting-code').val(code);
}

