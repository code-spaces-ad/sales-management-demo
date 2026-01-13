/**
 * クリア処理
 */
window.clearInput = function () {
    // エラーメッセージクリア
    $('.invalid-feedback').remove();
    // エラー枠クリア
    $('.is-invalid').removeClass('is-invalid');

    //select
    $('.clear-select').prop('selectedIndex', 0).change();
    //value
    $('.clear-value').val('');
    //check
    $('.clear-check').removeAttr('checked').prop('checked', false);
    //date
    $('.clear-date-start').val(getBeginningMonth());
    $('.clear-date-end').val(getEndMonth());

    // 得意先
    $('.input-customer').prop('selectedIndex', 0).change();
    changeCustomer();
    changeStartCustomer();
    changeEndCustomer();
    // 仕入先
    $('.input-supplier').prop('selectedIndex', 0).change();
    changeSupplier();
    // 部門
    $('.input-department').prop('selectedIndex', 0).change();
    changeDepartment();
    // 事業所
    $('.input-office-facility').prop('selectedIndex', 0).change();
    changeOfficeFacility();
    // 商品
    $('.input-product').prop('selectedIndex', 0).change();
    changeStartProduct();
    changeEndProduct();
    changeProduct();

    let now = new Date();
    let yearMonth = now.getFullYear() + '-' + ('00' + (now.getMonth() + 1)).slice(-2);
    $('#purchase_date').val(yearMonth);
    $('#charge_date').val(yearMonth);
    $('#closing_date').val(0);
    let today = now.getFullYear() + '-' + ('00' + (now.getMonth() + 1)).slice(-2) + '-' + ('00' + now.getDate()).slice(-2);
    $('#issue_date').val(today);

    $('.input-code-start').val('');
    $('.input-code-end').val('');
    $('.input-name').val('');
    $('.input-name-kana').val('');
    $('.input-customer-code-start').val('');
    $('.input-customer-code-end').val('');
    $('.input-customer-name').val('');
    $('.input-customer-name-kana').val('');
    $('.input-branch-name').val('');
    $('.input-recipient-name').val('');
    $('.input-login-id').val('');

    $('.input-category-code-start').val('');
    $('.input-category-code-end').val('');
    $('.input-sub-category-code-start').val('');
    $('.input-sub-category-code-end').val('');

    // 帳票関連
    $('#year_month').val(yearMonth);
    $('.clear-date-today-start').val(today);
    $('.clear-date-today-end').val(today);

    // 部門hidden判定で事業所フィルタ切替
    window.selectByOfficeFacility();

    // 再描画
    // $('#searchForm').submit();
}
