$(document).ready(function () {
    // Test if the header is actually present
    if ($('#barBottomContainer').length > 0) {
        STUDIP.HeaderMagic.enable();
    }
}).on('click', '#avatar-arrow', function (event) {
    event.stopPropagation();
    $('#header_avatar_menu .action-menu-icon').trigger('mousedown');
});
