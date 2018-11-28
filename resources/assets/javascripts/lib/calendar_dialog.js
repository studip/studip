import Dialog from './dialog.js';

const CalendarDialog = {
    closeMps: function(form) {
        var added_users = [];
        jQuery('#calendar-manage_access_selectbox option:selected').each(function() {
            added_users[added_users.length] = jQuery(this).attr('value');
        });
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/single/add_users/',
            data: {
                added_users: added_users
            },
            type: 'post'
        });
        jQuery(form)
            .closest('.ui-dialog-content')
            .dialog('close');
        Dialog.fromURL(jQuery('#calendar-open-manageaccess').attr('href'));
        return false;
    },

    removeUser: function(element) {
        var url = jQuery(element).attr('href');
        jQuery(element).removeAttr('href');
        jQuery.ajax({
            url: url,
            type: 'get',
            success: function() {
                var head_tr = jQuery(element)
                    .closest('tr')
                    .prev('.calendar-user-head');
                jQuery(element)
                    .closest('tr')
                    .remove();
                if (head_tr.nextUntil('.calendar-user-head').length === 0) {
                    head_tr.remove();
                }
            }
        });
        return false;
    },

    addException: function() {
        var exc_date = jQuery('#exc-date').val();
        var exists = jQuery('#exc-dates input').is("input[value='" + exc_date + "']");
        if (!exists) {
            var compiled = _.template(
                '<li><label>' +
                    '<input type="checkbox" name="del_exc_dates[]" value="<%- excdate %>" style="display: none">' +
                    '<span><%- excdate %><img src="' +
                    STUDIP.ASSETS_URL +
                    'images/icons/blue/trash.svg' +
                    '"></span></label>' +
                    '<input type="hidden" name="exc_dates[]" value="<%- excdate %>">' +
                    '</li>'
            );
            jQuery('#exc-dates').append(compiled({ excdate: exc_date, link: '' }));
        }
        return false;
    }
};

export default CalendarDialog;
