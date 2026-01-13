
/**
 * 送信種別を変更した時のイベント
 *
 * @param target
 */
window.changeUrlSelectPosSendApiId = function (target) {
    let selectedOption = target.options[target.selectedIndex];
    let url = selectedOption.dataset.url;
    $('.pos-api-input-url').val(url);
    console.log(url);
};

/**
 * 受信種別を変更した時のイベント
 *
 * @param target
 */
window.changeUrlSelectPosReceiveApiId = function (target) {
    let selectedOption = target.options[target.selectedIndex];
    let url = selectedOption.dataset.url;
    $('.pos-api-input-url').val(url);
    console.log(url);
};

/**
 * POS送信用 API送信
 */
window.posSendDataApi = function () {
    $('.invalid-feedback').remove();                // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア
    $('.alert').remove();
    $('.pos-api-input-param-json').val('');
    $('.pos-api-return-value').val('');

    startPreloader();

    let datalist = {};
    let url = '';
    $("#searchForm").find(":input:not(textarea)").serialize().split('&').forEach(item => {
        let [name, value] = item.split('=');
        name = decodeURIComponent(name);
        value = decodeURIComponent(value);

        // 送信先URL取得
        if (name === 'url') {
            url = value;
        }

        // パラメータ設定
        if (name === 'target_date') {
            if (value.length > 0) {
                value = value.replace('T', ' ') + ':00';
            }
            datalist[name] = value;
        }
    });

    // 配列の内容をパラメータに表示する
    const jsonText = JSON.stringify(datalist, null, 2);
    $('.pos-api-input-param-json').val(jsonText);

    if (url === undefined || url === null) {
        stopPreloader();
        alert('送信種別が選択されていません');
        return;
    }

    // API送信処理
    posSendDataApiAjax(url, datalist);
};

/**
 * POS送信用 API送信処理
 */
window.posSendDataApiAjax = function (url, datalist) {
    $.ajax({
        url: url,
        type: "GET",
        data: datalist,
        cache: false,
    }).done(function(data, textStatus, jqXHR) {
        console.log(data);
        $('.pos-api-return-value').val(JSON.stringify(data, null, 4));
        stopPreloader();
    }).fail(function(jqXHR, textStatus, errorThrown) {
        let msg = 'Error!!! : ' + jqXHR.status + ' : ' + textStatus;
        console.log(msg);
        $('.pos-api-return-value').val(msg);
        stopPreloader();
    });
};

/**
 * POS受信用 API送信
 */
window.posReceiveDataApi = function () {
    $('.invalid-feedback').remove();                // エラーメッセージクリア
    $('.is-invalid').removeClass('is-invalid');     // エラー枠クリア
    $('.alert').remove();
    $('.pos-api-input-param-json').val('');
    $('.pos-api-return-value').val('');

    startPreloader();

    let datalist = {};
    let url = '';
    $("#searchForm").find(":input:not(textarea)").serialize().split('&').forEach(item => {
        let [name, value] = item.split('=');
        name = decodeURIComponent(name);
        value = decodeURIComponent(value);

        // 送信先URL取得
        if (name === 'url') {
            url = value;
        }

        // パラメータ設定
        if (name === 'target_date') {
            if (value.length > 0) {
                value = value.replace('T', ' ') + ':00';
            }
            datalist[name] = value;
        }
        if (name === 'store_id' || name === 'regi_id' || name === 'inventory_status') {
            datalist[name] = value;
        }
    });

    // 配列の内容をパラメータに表示する
    const jsonText = JSON.stringify(datalist, null, 2);
    $('.pos-api-input-param-json').val(jsonText);

    if (url === undefined || url === null) {
        stopPreloader();
        alert('受信種別が選択されていません');
        return;
    }

    // API送信処理
    posSendDataApiAjax(url, datalist);
};

/**
 * POS受信用 API送信処理
 */
window.posReceiveDataApiAjax = function (url, datalist) {
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: url,
        type: 'POST',
        cache: false,
        dataType: 'json',
        data: datalist,
    }).done(function(data, textStatus, jqXHR) {
        console.log(data);
        $('.pos-api-return-value').val(JSON.stringify(data, null, 4));
        stopPreloader();
    }).fail(function(jqXHR, textStatus, errorThrown) {
        let msg = 'Error!!! : ' + jqXHR.status + ' : ' + textStatus;
        console.log(msg);
        $('.pos-api-return-value').val(msg);
        stopPreloader();
    });
};
