/**
 * @copyright © 2025 CodeSpaces
 */

/**
 * キーイベント変更
 */
$('input,select,button').keydown(function (e) {
    let stopKeys = ['登録', '更新', '検索', 'ログイン', 'OK'];    // タブ移動対象外キー
    let skipKeys = ['クリア', '削除'];    // スキップ対象キー
    let skipDataToggles = ['modal'];    // スキップ対象部品
    let skipValues = ['クリア', 'clear'];// スキップ対象値
    let targetElm = 'input,button,select';
    $targetElm = $(targetElm);
    let countIndex = $targetElm.length;

    // Enter キーの場合は、タブ移動にする。
    if (e.which === 13) {
        if (stopKeys.indexOf($(this).text().trim()) >= 0) {
            // 登録ボタン等の場合は、タブ移動させない
            return;
        }

        // イベントをキャンセル
        e.preventDefault();

        let Index = $targetElm.index(this);
        let nextIndex = Index + 1;

        for (let i = Index + 1; i < countIndex; i++) {
            // disable / readonly の場合、次の要素にスキップ
            if ($targetElm.eq(nextIndex).is(':disabled') || $targetElm.eq(nextIndex).attr('readonly')) {
                nextIndex = i + 1;
                continue;
            }
            // スキップ対象キーの場合、次の要素にスキップ
            if (skipKeys.indexOf($targetElm.eq(nextIndex).text().trim()) >= 0) {
                nextIndex = i + 1;
                continue;
            }
            // スキップ対象キーの場合、次の要素にスキップ
            if (skipDataToggles.indexOf($targetElm.eq(nextIndex).attr('data-toggle')) >= 0) {
                nextIndex = i + 1;
                continue;
            }
            // スキップValueの場合、次の要素にスキップ
            if (skipValues.indexOf($targetElm.eq(nextIndex).val()) >= 0) {
                nextIndex = i + 1;
                continue;
            }

            // 表示されている要素かチェック
            if ($targetElm.eq(nextIndex).is(':visible')) {
                break;
            }

            nextIndex = i + 1;
        }

        if (nextIndex < countIndex) {
            $targetElm.eq(nextIndex).focus();    // 次の要素へフォーカスを移動
        } else {
            // 保存ボタン取得
            let targetBtn = $(".btn").filter(function () {
                return $(this).attr('data-target') === '#confirm-store';
            }).eq(0);

            if (targetBtn.length) {
                // 保存ボタンがあれば、そちらにフォーカスさせる
                targetBtn.focus();
            } else {
                // 最初の（表示されている）要素にフォーカスを移動
                $('input:visible:enabled,select:visible:enabled').eq(0).focus();
            }
        }
    }
});
