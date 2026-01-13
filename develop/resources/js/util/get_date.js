/**
 * 月初取得
 *
 * @returns {string}
 */
window.getBeginningMonth = function () {
    let date = new Date();
    date.setDate(1);

    return date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
}

/**
 * 月末取得
 *
 * @returns {string}
 */
window.getEndMonth = function () {
    let date = new Date();
    date.setDate(1);
    date.setMonth(date.getMonth() + 1);
    date.setDate(0);
    return date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
}
