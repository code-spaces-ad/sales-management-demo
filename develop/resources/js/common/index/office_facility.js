/**
 * 部門のhidden判定
 */
function isDepartmentHidden() {

    return $('.input-department-id').length > 0 && $('.input-department-id').attr('type') === 'hidden';
}

// 部門がhidden時はformat、通常はfilter
window.selectByOfficeFacility = function () {
    if (isDepartmentHidden()) {
        window.formatOfficeFacility();
    }

    if (!isDepartmentHidden()) {
        window.filterOfficeFacility();
    }
}

/**
 * 事業所コード変更時の処理
 */
window.changeOfficeFacilityCode = function (target) {
    let targetValue = $(target).val();

    if (isNaN(targetValue)) {
        targetValue = -1;
    }

    let targetOption = ".input-office-facility-select option[data-code='" + parseInt(targetValue) + "']";

    if ($(targetOption).not('.d-none').length === 1) {
        // 対象コードがあれば、そのリストを選択状態にする
        $(targetOption).prop('selected', true);
    } else {
        // 対象コードがなければ、最初のリストを選択状態にする
        $(target).val('');  // コード枠はクリア
        $('.input-office-facility-select').prop('selectedIndex', 0).change();
    }

    // select2チェンジイベント
    $('.select2_search').trigger('change');
}

/**
 * 事業所セレクトボックス変更処理
 */
window.changeOfficeFacility = function () {
    let code = $('.input-office-facility-select option:selected').data('code');
    let department_id = $('.input-office-facility-select option:selected').data('department-id');

    function zeroPad(num, places) {
        var zero = places - num.toString().length + 1;
        return Array(+(zero > 0 && zero)).join("0") + num;
    }

    if (code) {
        code = zeroPad(code, 4)
    }
    // 選択された事業所のコードをセット
    $('.input-office-facility-code').val(code);

    $('.input-office-facility-select').prop('disabled', false);

    if (!code) {
        $('.input-office-facilities-select').prop('disabled', true);
    }

    // 部署IDセット
    $('.input-department-id').val(department_id);
}

/**
 * 事業所セレクトボックスフィルタリング処理
 */
window.filterOfficeFacility = function () {
    // 全体を一旦クリア
    $(".hidden-office-facility").unwrap();
    $(".input-office-facility-select option").removeClass('hidden-office-facility');

    let targetDepartmentId = $('.input-department-select option:selected').val();
    if (targetDepartmentId !== '') {
        $(".input-office-facility-select option").each(function () {
            if ($(this).data('department-id') === undefined) {
                return true;
            }
            if ($(this).data('department-id') !== parseInt(targetDepartmentId)) {
                // 得意先IDが不一致は非表示セット
                $(this).addClass('hidden-office-facility');
                $(this).wrap("<span class='d-none'></span>");
            }
        });
    }

    let selectIndex = $('.input-office-facility-select').prop('selectedIndex');
    let filterFirstIndex = $(".input-office-facility-select option:not(.hidden-office-facility)").first().index();
    // 選択状態でない、または選択状態が非表示の場合
    if (selectIndex === -1 || $('.input-office-facility-select :selected').hasClass('hidden-office-facility')) {
        // 未選択状態に変更する
        $(".input-office-facility-select option:selected").prop("selected", false);
        $('.input-office-facility-select').prop("selectedIndex", filterFirstIndex).change();

    }
}

/**
 * 事業所セレクトボックスの初期化処理(売掛金仕訳帳CSV出力軽減)
 */
window.formatOfficeFacility = function () {
    const select = $('.input-office-facility-select');
    const options = select.find('option');
    const selectedDepartmentId = parseInt($('.input-department-select').val(), 10);
    // すべての事業所を一度表示状態に戻す
    options.removeClass('hidden-office-facility');

    // 部署IDが選択されている場合、その部署以外の事業所は非表示にする
    if (!isNaN(selectedDepartmentId)) {
        options.each(function () {
            const departmentId = parseInt($(this).data('department-id'), 10);
            if (!isNaN(departmentId) && departmentId !== selectedDepartmentId) {
                $(this).addClass('hidden-office-facility');
            }
        });
    }
    // 選択されている事業所が非表示の場合、使用する事業所に変更
    const currentSelected = select.find('option:selected');
    if (currentSelected.hasClass('hidden-office-facility') || currentSelected.length === 0) {
        const useOption = select.find('option:not(.hidden-office-facility)').first();
        if (useOption.length > 0) {
            select.val(useOption.val()).trigger('change');
        }

        if (useOption.length === 0) {
            select.val('').trigger('change'); // 該当なしの場合は未選択に
        }
    }


}


