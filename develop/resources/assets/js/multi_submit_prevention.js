/**
 * @copyright © 2025 CodeSpaces
 */

// Form Submit Button Click Event
$('[type="submit"]').on('touchstart click', function(event) {
    event.preventDefault();
    event.stopPropagation();

    // submit 多重送信防止
    $(this).prop('disabled', true);

    // フォームを送信する
    $(this).closest('form').submit();

    var elem = $(this);
    setTimeout(function () {
        // disabled を元に戻す
        elem.prop('disabled', false);
    }, 1000);
});
