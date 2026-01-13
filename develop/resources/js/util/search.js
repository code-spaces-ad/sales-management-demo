/**
 * 住所検索
 */
window.searchAddress = function () {
    // ローディング&非活性
    $('#address-spinner i').hide();
    $('#address-spinner div').show();
    $('#address-spinner').prop('disabled', true);

    let postal_code = $('#postal_code1').val() + $('#postal_code2').val();

    axios.post(`/api/search_address/`, {
        'postal_code': postal_code
    })
        .then(response => {
            let address = response.data[0];
            if (!address) {
                alert("住所が取得できませんでした");
            } else {
                $('.input-address1').val(address);
            }
        })
        .catch(error => {
            alert("住所が取得できませんでした");
            console.error(error);
        })
        .finally(() => {
            // ローディング&非活性　解除
            $('#address-spinner i').show();
            $('#address-spinner div').hide();
            $('#address-spinner').prop('disabled', false);
        });
}

/**
 * 空番検索
 */
window.searchAvailableNumber = function (type, parent_key = null, parent_id = null) {
    // ローディング&非活性
    $('#code-spinner i').hide();
    $('#code-spinner div').show();
    $('#code-spinner').prop('disabled', true);

    if( parent_key !== ''  && parent_id === '' ){
        // key だけ入っている時は「1」とする
        parent_id = '0';
    }

    axios.post(`/api/search_available_number/`, {
        'type': type,
        'available_number': $('.input-code').val(),
        'parent_key': parent_key,
        'parent_id': parent_id
    })
        .then(response => {
            let code = response.data;
            if (!code) {
                alert("空番が取得できませんでした");
            } else {
                $('.input-code').val(code);
            }
        })
        .catch(error => {
            alert("空番が取得できませんでした");
            console.error(error);
        })
        .finally(() => {
            // ローディング&非活性　解除
            $('#code-spinner i').show();
            $('#code-spinner div').hide();
            $('#code-spinner').prop('disabled', false);
        });
}

/**
 * 明きソート番号検索
 */
window.searchAvailableSortNumber = function (type) {
    // ローディング&非活性
    $('#sort-spinner i').hide();
    $('#sort-spinner div').show();
    $('#sort-spinner').prop('disabled', true);

    axios.post(`/api/search_available_sort_number/`, {
        'type': type,
        'available_number': $('.input-sort-code').val()
    })
        .then(response => {
            let code = response.data;
            if (!code) {
                alert("ソート番号が取得できませんでした");
            } else {
                $('.input-sort-code').val(code);
                $('.input-sort-code').focus();
            }
        })
        .catch(error => {
            alert("ソート番号が取得できませんでした");
            console.error(error);
        })
        .finally(() => {
            // ローディング&非活性　解除
            $('#sort-spinner i').show();
            $('#sort-spinner div').hide();
            $('#sort-spinner').prop('disabled', false);
        });
}
