/**
 * Sidebar Menu
 *
 * @copyright © 2025 CodeSpaces
 */

/**
 * Sidebar Menu ドロップダウン クリックイベント
 * Level 1
 */
$('.sidebar-dropdown > a').click(function () {
    $('.sidebar-submenu').slideUp(200);
    $('.sidebar-submenu-2').slideUp(200);
    $('.sidebar-dropdown-2').removeClass('active');
    if ($(this).parent().hasClass('active')) {
        $(this).parent().removeClass('active');
    } else {
        $('.sidebar-dropdown').removeClass('active');
        $(this).next('.sidebar-submenu').slideDown(200);
        $(this).parent().addClass('active');
    }
});

/**
 * Sidebar Menu ドロップダウン クリックイベント
 * Level 2
 */
$('.sidebar-dropdown-2 > a').click(function () {
    $('.sidebar-submenu-2').slideUp(200);
    if ($(this).parent().hasClass('active')) {
        $(this).parent().removeClass('active');
    } else {
        $('.sidebar-dropdown-2').removeClass('active');
        $(this).next('.sidebar-submenu-2').slideDown(200);
        $(this).parent().addClass('active');
    }
});

/**
 * Sidebar Toggle 閉じる
 */
$('#close-sidebar').click(function () {
    $('.page-wrapper').removeClass('toggled');
    $('.sidebar-submenu').slideUp(200);
    $('.sidebar-dropdown').removeClass('active');
});

/**
 * Sidebar Toggle 開く
 */
$('#show-sidebar').click(function () {
    $('.page-wrapper').addClass('toggled');
});
