/**
 * 取得したスクロール位置をセット
 *
 * @param cookieName
 */
window.setCookie = function (cookieName) {
    document.getElementById('page_content_id').scrollTop = getCookie(cookieName);
    document.cookie = cookieName + '= 0';
}

/**
 * cookieに保存されているスクロール位置を取得
 *
 * @param cookieName
 */
window.getCookie = function (cookieName) {
    let cookie = {};
    document.cookie.split(';').forEach(function (el) {
        let [key, value] = el.split('=');
        cookie[key.trim()] = value;
    })
    return cookie[cookieName];
}

/**
 * cookieに現在のスクロール位置を保存
 *
 * @param cookie
 */
window.checkingClickedOrNot = function (cookie) {
    document.cookie = cookie + '=' + document.getElementById('page_content_id').scrollTop;
}
