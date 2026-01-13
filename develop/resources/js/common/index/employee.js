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

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 4)
    }
    // 選択された担当者のコードをセット
    $('.input-employee-code').val(code);

    $('.input-customer-start-select').prop('disabled', false);

    if (!code) {
        $('.input-customer-start-select').prop('disabled', true);
    }

    $('.input-customer-end-select').prop('disabled', false);

    if (!code) {
        $('.input-customer-end-select').prop('disabled', true);
    }

    // 得意先セレクトボックスフィルタリング
    filterCustomer();
}

/**
 * 得意先セレクトボックスフィルタリング
 */
window.filterCustomer = function () {
    // 全体を一旦クリア
    $(".hidden-customer").unwrap();
    $(".input-customer-start-select option").removeClass('hidden-customer');
    $(".input-customer-end-select option").removeClass('hidden-customer');

    let targetEmployeeId = $('.input-employee-select option:selected').val();
    if (targetEmployeeId !== '') {
        $(".input-customer-start-select option").each(function () {
            // 得意先optionのdata-employee-id属性と比較
            if ($(this).data('employee-id') !== parseInt(targetEmployeeId)) {
                $(this).addClass('hidden-customer');
                $(this).wrap("<span class='d-none'></span>");
            }
        });

        $(".input-customer-end-select option").each(function () {
            if ($(this).data('employee-id') !== parseInt(targetEmployeeId)) {
                $(this).addClass('hidden-customer');
                $(this).wrap("<span class='d-none'></span>");
            }
        });
    }

    // start-selectの選択状態チェック
    let selectIndex = $('.input-customer-start-select').prop('selectedIndex');
    if (selectIndex === -1 || $('.input-customer-start-select :selected').hasClass('hidden-customer')) {
        $(".input-customer-start-select option:selected").prop("selected", false);
        // 担当者に紐づく最初の得意先を選択
        if (targetEmployeeId !== '') {
            let firstVisibleOption = $('.input-customer-start-select option:not(.hidden-customer)').first();
            if (firstVisibleOption.length > 0 && firstVisibleOption.val() !== '') {
                firstVisibleOption.prop('selected', true);
            } else {
                $('.input-customer-start-select').prop('selectedIndex', 0);
            }
        } else {
            $('.input-customer-start-select').prop('selectedIndex', 0);
        }
        $('.input-customer-start-select').change();
    }

    // end-selectの選択状態チェック
    let selectIndexEnd = $('.input-customer-end-select').prop('selectedIndex');
    if (selectIndexEnd === -1 || $('.input-customer-end-select :selected').hasClass('hidden-customer')) {
        $(".input-customer-end-select option:selected").prop("selected", false);
        // 担当者に紐づく最初の得意先を選択
        if (targetEmployeeId !== '') {
            let firstVisibleOption = $('.input-customer-end-select option:not(.hidden-customer)').first();
            if (firstVisibleOption.length > 0 && firstVisibleOption.val() !== '') {
                firstVisibleOption.prop('selected', true);
            } else {
                $('.input-customer-end-select').prop('selectedIndex', 0);
            }
        } else {
            $('.input-customer-end-select').prop('selectedIndex', 0);
        }
        $('.input-customer-end-select').change();
    }
}
