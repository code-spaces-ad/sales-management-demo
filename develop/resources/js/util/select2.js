/**
 * select2検索条件
 */
window.select2Matcher = function (params, data) {
    if ($.trim(params.term) === '') {
        return data;
    }
    //name検索
    let name = $(data.element).data('name');
    if (name === undefined) {
        return null;
    }
    if (name.toUpperCase().indexOf(params.term) === 0) {
        return data;
    }
    //name_kana検索
    let nameKana = $(data.element).data('nameKana');
    if (nameKana === undefined) {
        return null;
    }
    if (nameKana.toUpperCase().indexOf(params.term) === 0) {
        return data;
    }
    //code検索
    let code = $(data.element).data('code');
    if (code === undefined) {
        return null;
    }
    if (code.toString().toUpperCase().indexOf(params.term) === 0) {
        return data;
    }

    return null;
}
