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

    //仕入と納品を非表示
    $('[data-name="仕入"]').addClass('d-none');
    $('[data-name="納品"]').addClass('d-none');

    // コードにフォーカス
    $('.input-code').focus();

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
        $(this).get(0).type = 'number';

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
        $(this).get(0).type = 'text';
        // 数量セット
        $(this).val(calcQuantity.toLocaleString(undefined, {
            minimumFractionDigits: digit,
            maximumFractionDigits: digit
        }));
    });

    /**
     * from_warehouse_idとto_warehouse_idの書き換え
     */
    $(function () {
        let warehouse_id = $('[name="selected_warehouse_id"]').val();
        let inventory_in = $('[data-name="仕入"]').data('id');
        let inventory_out = $('[data-name="納品"]').data('id');

        //ラジオボタン OR セレクトボックス変更時の書き換え
        $('[name="inout_data"], [name="warehouse_id"]').on("change", function () {
            let from_warehouse_id = $('.input-warehouse-select option:selected').data('id');
            let inout_data = $('[name="inout_data"]:checked').val();
            let stock = $('.input-warehouse-select option:selected').data('stock');

            // ボタン活性化
            enableButtonsExclusionReturn();
            //d-none追加
            $('.selected_stock_error').addClass('d-none');
            $('.stock_error').addClass('d-none');

            //「出庫」が選択されていた且つ、在庫数が0の時のボタン非活性
            if (inout_data === 'issue' && $('.selected_stock').text() == 0) {
                //ボタンの非活性
                disableButtonsExclusionReturn();
                //d-none削除
                $('.selected_stock_error').removeClass('d-none');
            }

            // 選択された倉庫のコードをセット
            if (inout_data === 'entry' && stock === 0) {
                // ボタン非活性
                disableButtonsExclusionReturn();
                //d-none削除
                $('.stock_error').removeClass('d-none');
            }

            $('[name="from_warehouse_id"]').val(from_warehouse_id);
            $('[name="to_warehouse_id"]').val(warehouse_id);

            //「出庫」が選択されていた且つ、倉庫名が選択されていない時の書き換え
            if (inout_data === 'issue' && $('[name="warehouse_id"]').val() === '') {
                $('[name="from_warehouse_id"]').val(warehouse_id);
                $('[name="to_warehouse_id"]').val(inventory_out);
                return;
            }
            //「入庫」が選択されていた且つ、倉庫名が選択されていない時の書き換え
            if (inout_data === 'entry' && $('[name="warehouse_id"]').val() === '') {
                $('[name="from_warehouse_id"]').val(inventory_in);
                $('[name="to_warehouse_id"]').val(warehouse_id);
                return;
            }
            //「出庫」が選択されていた時の書き換え
            if (inout_data === 'issue') {
                $('[name="from_warehouse_id"]').val(warehouse_id);
                $('[name="to_warehouse_id"]').val(from_warehouse_id);
            }
        });
    });
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
 * 登録更新処理
 */
window.store = function () {
    // モーダル閉じる
    $('#confirm-store').modal('hide');

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
 * ボタン非活性
 */
window.disableButtons = function () {
    // ボタン非活性
    $('#store').prop('disabled', true);
    $('#clear').prop('disabled', true);
    $('#delete').prop('disabled', true);
    $('#return').prop('disabled', true);
}

/**
 * ボタン活性化
 */
window.enableButtons = function () {
    // ボタン非活性
    $('#store').prop('disabled', false);
    $('#clear').prop('disabled', false);
    $('#delete').prop('disabled', false);
    $('#return').prop('disabled', false);
}

/**
 * ボタン非活性
 */
window.disableButtonsExclusionReturn = function () {
    // ボタン非活性
    $('#store').prop('disabled', true);
    $('#clear').prop('disabled', true);
    $('#delete').prop('disabled', true);
}

/**
 * ボタン活性化
 */
window.enableButtonsExclusionReturn = function () {
    // ボタン非活性
    $('#store').prop('disabled', false);
    $('#clear').prop('disabled', false);
    $('#delete').prop('disabled', false);
}

/**
 * ローディング切替(登録・更新）
 */
window.changeStoreLoading = function () {
    // ローディング切替
    $('#store i').hide();
    $('#store div').show();
}

/**
 * ローディング切替(削除）
 */
window.changeDeleteLoading = function () {
    // ローディング切替
    $('#delete i').hide();
    $('#delete div').show();
}
