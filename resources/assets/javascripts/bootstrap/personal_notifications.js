$(document).on('click', '#notification_list .mark_as_read', STUDIP.PersonalNotifications.markAsRead);

STUDIP.domReady(() => {
    STUDIP.PersonalNotifications.initialize();

    $('#notification_container .mark-all-as-read')
        .click(STUDIP.PersonalNotifications.markAllAsRead);
    $('#notification_list')
        .mouseenter(STUDIP.PersonalNotifications.setSeen);
});
