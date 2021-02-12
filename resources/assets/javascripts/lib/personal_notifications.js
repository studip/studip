import Favico from 'favico.js';
import Cache from './cache.js';
import PageLayout from './page_layout.js';

var stack = {};
var audio_notification = false;
var directlydeleted = [];
var favicon = null;

function updateFavicon(text) {
    if (favicon === null) {
        var valid = $('head')
            .find('link[rel=icon]')
            .first();
        $('head')
            .find('link[rel*=icon]')
            .not(valid)
            .remove();

        favicon = new Favico({
            bgColor: '#d60000',
            textColor: '#fff',
            fontStyle: 'normal',
            fontFamily: 'Lato',
            position: 'right',
            type: 'rectangle'
        });
    }
    favicon.badge(text);
}

// Wrapper function that creates a desktop notification from given data
function create_desktop_notification(data) {
    var notification = new Notification(STUDIP.STUDIP_SHORT_NAME, {
        body: data.text,
        icon: data.avatar,
        tag: data.id
    });
    notification.addEventListener('click', () => {
        location.href = STUDIP.URLHelper.getURL(`dispatch.php/jsupdater/mark_notification_read/${notification.tag}`);
    });
}

// Handler for all notifications received by an ajax request
function process_notifications({ notifications }) {
    var cache = Cache.getInstance('desktop.notifications');
    var ul = $('<ul/>');
    var changed = false;
    var new_stack = {};

    notifications.forEach(notification => {
        if (directlydeleted.indexOf(notification.personal_notification_id) !== -1) {
            return;
        }

        ul.append(notification.html);

        var id = $('.notification:last', ul).data().id;
        new_stack[id] = notification;
        if (notification.html_id) {
            $(`#${notification.html_id}`).on('mouseenter', PersonalNotifications.isVisited);
        }

        changed = changed || !stack.hasOwnProperty(id);

        // Check if notifications should be sent (depends on the
        // Notification itself and session storage)
        if (
            !window.hasOwnProperty('Notification')
            || Notification.permission !== 'granted'
            || cache.has(notification.id)
        ) {
            return;
        }

        // If it's okay let's create a notification
        create_desktop_notification(notification);

        cache.set(id, true);
    });

    // Anything changed? Replace stack and display
    if (changed || Object.keys(stack).length !== Object.keys(new_stack).length) {
        stack = new_stack;
        $('#notification_list > ul').replaceWith(ul);
    }

    PersonalNotifications.update();
    directlydeleted = [];
}

const PersonalNotifications = {
    initialize () {
        if ($('#notification_marker').length > 0) {
            $('#notification_list .notification').map(function() {
                var data = $(this).data();
                stack[data.id] = data;
            });

            STUDIP.JSUpdater.register(
                'personalnotifications',
                process_notifications,
                null,
                60000
            );

            if ($('#audio_notification').length > 0) {
                audio_notification = $('#audio_notification').get(0);
                audio_notification.load();
            }

            if ('Notification' in window) {
                $('#notification_list .enable-desktop-notifications')
                    .toggle(Notification.permission === 'default')
                    .click(STUDIP.PersonalNotifications.activate);
            }
        }
    },
    activate () {
        Promise.resolve(Notification.requestPermission()).then(permission => {
            $('#notification_list .enable-desktop-notifications')
                .toggle(permission === 'default');
        });
    },
    markAsRead (event) {
        var notification = $(this).closest('.notification'),
            id = notification.data().id;
        PersonalNotifications.sendReadInfo(id, notification);
        return false;
    },
    markAllAsRead (event) {
        var notifications = $(this)
            .parent()
            .find('.notification');
        PersonalNotifications.sendReadInfo('all', notifications);
        return false;
    },
    sendReadInfo (id, notification) {
        $.get(STUDIP.URLHelper.getURL(`dispatch.php/jsupdater/mark_notification_read/${id}`)).done(() => {
            if (notification) {
                var count = notification.length;
                notification.toggle('blind', 'fast', function() {
                    var data = $(this).data();
                    delete stack[data.id];
                    $(this).remove();

                    count -= 1;
                    if (count === 0) {
                        PersonalNotifications.update();
                    }
                });
            }
        });
    },
    update () {
        var count = _.values(stack).length;
        var old_count = parseInt($('#notification_marker').text(), 10);
        var really_new = 0;
        $('#notification_list > ul > li').each(function() {
            if (parseInt($(this).data('timestamp'), 10) > parseInt($('#notification_marker').data('lastvisit'), 10)) {
                really_new += 1;
            }
        });
        if (really_new > 0) {
            $('#notification_marker')
                .data('seen', false)
                .addClass('alert');
            PageLayout.title_prefix = '(!) ';
        } else {
            $('#notification_marker').removeClass('alert');
            PageLayout.title_prefix = '';
        }
        if (count) {
            $('#notification_container').addClass('hoverable');
            if (count > old_count && audio_notification !== false) {
                audio_notification.play();
            }
        } else {
            $('#notification_container').removeClass('hoverable');
        }
        if (old_count !== count) {
            $('#notification_marker').text(count);
            updateFavicon(count);
            $('#notification_container .mark-all-as-read').toggleClass('notification_hidden', count < 2);
        }
    },
    isVisited () {
        const id = this.id;
        $.each(stack, (index, notification) => {
            if (notification.html_id === id) {
                PersonalNotifications.sendReadInfo(notification.personal_notification_id);

                delete stack[index];

                $(`.notification[data-id=${notification.personal_notification_id}]`).fadeOut(function () {
                    $(this).remove();
                });

                directlydeleted.push(notification.personal_notification_id);

                PersonalNotifications.update();
            }
        });
    },
    setSeen () {
        if ($('#notification_marker').data('seen')) {
            return;
        }
        $('#notification_marker').data('seen', true);

        $.get(STUDIP.URLHelper.getURL('dispatch.php/jsupdater/notifications_seen')).then(time => {
            $('#notification_marker')
                .removeClass('alert')
                .data('lastvisit', time);
        });
    }
};

export default PersonalNotifications;
