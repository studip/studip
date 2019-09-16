STUDIP.domReady(() => {
    // Test if the header is actually present
    if ($('#barBottomContainer').length > 0) {
        STUDIP.HeaderMagic.enable();
    }
});

$(document).on('click', '#avatar-arrow', function (event) {
    STUDIP.ActionMenu.create('#header_avatar_menu .action-menu', 'avatar-menu', false).toggle();

    event.stopPropagation();
});
