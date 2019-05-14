import Calendar from './calendar.js';
import Dialog from './dialog.js';

const Schedule = {
    inst_changed: false,

    /**
     * this function is called, when an entry shall be created in the calendar
     *
     * @param  object  the empty entry in the calendar
     * @param  int     the day that has been clicked
     * @param  int     the start-hour that has been clicked
     * @param  int     the end-hour that has been chosen
     */
    newEntry: function(entry, day, start_hour, end_hour) {
        /*
        // do not allow creation of new entry, if one of the following popups is visible!
        if (jQuery('#edit_sem_entry').is(':visible') ||
            jQuery('#edit_entry').is(':visible') ||
            jQuery('#edit_inst_entry').is(':visible')) {
            jQuery(entry).remove();
            return;
        }
        */

        // if there is already an entry set, kick him first before showing a new one
        if (this.entry) {
            jQuery(this.entry).fadeOut('fast');
            jQuery(this.entry).remove();
        }

        this.entry = entry;

        if (!Schedule.new_entry_template) {
            jQuery.get(STUDIP.URLHelper.getURL('dispatch.php/calendar/schedule/entry'), function(data) {
                Schedule.new_entry_template = data;
                Schedule.showEntryDialog(Schedule.new_entry_template, day, start_hour, end_hour);
            });
        } else {
            Schedule.showEntryDialog(Schedule.new_entry_template, day, start_hour, end_hour);
        }
    },

    /**
     * this function is called, when an entry shall be created in the calendar
     * and the template-data has been loaded
     *
     * @param  string  the html for the new-entry dialog
     * @param  int     the day that has been clicked
     * @param  int     the start-hour that has been clicked
     * @param  int     the end-hour that has been chosen
     */
    showEntryDialog: function(template, day, start_hour, end_hour) {
        // do not open dialog, if no new-entry-marker is present
        if ($('#schedule_entry_new').length === 0) return;

        Dialog.show(template, {
            title: 'Neuen Termin eintragen'.toLocaleString(),
            origin: this
        });

        $(this).on('dialog-close', function() {
            $('#schedule_entry_new').remove();
        });

        // fill values of overlay
        jQuery('input[name=entry_start]').val(start_hour + ':00');
        jQuery('input[name=entry_end]').val(end_hour + ':00');
        jQuery('select[name=entry_day]').val(parseInt(day) + 1);
    },

    /**
     * this function morphs from the quick-add box for adding a new entry to the schedule
     * to the larger box with more details to edit
     *
     * @return: void
     */
    showDetails: function() {
        // set the values for detailed view
        jQuery('select[name=entry_day]').val(Number(jQuery('#new_entry_day').val()) + 1);
        jQuery('input[name=entry_start_hour]').val(parseInt(jQuery('#new_entry_start_hour').val(), 10));
        jQuery('input[name=entry_start_minute]').val('00');
        jQuery('input[name=entry_end_hour]').val(parseInt(jQuery('#new_entry_end_hour').val(), 10));
        jQuery('input[name=entry_end_minute]').val('00');

        jQuery('input[name=entry_title]').val(jQuery('#entry_title').val());
        jQuery('textarea[name=entry_content]').val(jQuery('#entry_content').val());

        jQuery('#edit_entry_drag').html(jQuery('#new_entry_drag').html());

        // morph to the detailed view
        jQuery('#schedule_new_entry').animate(
            {
                left: Math.floor(jQuery(window).width() / 4), // for safari
                width: '50%',
                top: '180px'
            },
            500,
            function() {
                jQuery('#edit_entry').fadeIn(400, function() {
                    // reset the box
                    jQuery('#schedule_new_entry').css({
                        display: 'none',
                        left: 0,
                        width: '400px',
                        top: 0,
                        height: '230px',
                        'margin-left': 0
                    });
                });
            }
        );
    },

    /**
     * show a popup conatining the details of the passed seminar
     * at the passed cycle
     *
     * @param  string  the seminar to be shown
     * @param  string  the cycle-id of the regular time-entry to be shown
     *                 (a seminar can have multiple of these
     */
    showSeminarDetails: function(seminar_id, cycle_id) {
        jQuery.get(
            STUDIP.URLHelper.getURL('dispatch.php/calendar/schedule/entryajax/' + seminar_id + '/' + cycle_id),
            function(data) {
                Dialog.show(data, {
                    title: 'Veranstaltungsdetails'.toLocaleString()
                });
            }
        );

        Calendar.click_in_progress = false;
    },

    /**
     * show a popup with the details of a regular schedule entry with passed id
     *
     * @param  string  the id of the schedule-entry
     */
    showScheduleDetails: function(id) {
        jQuery.get(STUDIP.URLHelper.getURL('dispatch.php/calendar/schedule/entry/' + id), function(data) {
            Dialog.show(data, {
                title: 'Termindetails bearbeiten'.toLocaleString()
            });
        });

        Calendar.click_in_progress = false;
    },

    /**
     * show a popup with the details of a group entry, containing several seminars
     *
     * @param  string  the id of the grouped entry to be displayed
     */
    showInstituteDetails: function(id) {
        jQuery.get(STUDIP.URLHelper.getURL('dispatch.php/calendar/schedule/groupedentry/' + id + '/true'), function(
            data
        ) {
            Dialog.show(data, {
                title: 'Veranstaltungsdetails'.toLocaleString()
            });
        });

        Calendar.click_in_progress = false;
    },

    /**
     * hide a seminar-entry in the schedule (admin-version)
     *
     * @param  string  the seminar to be shown
     * @param  string  the cycle-id of the regular time-entry to be shown
     *                 (a seminar can have multiple of these
     */
    instSemUnbind: function(seminar_id, cycle_id) {
        Schedule.inst_changed = true;
        jQuery.ajax({
            type: 'GET',
            url: STUDIP.URLHelper.getURL(
                'dispatch.php/calendar/schedule/adminbind/' + seminar_id + '/' + cycle_id + '/0/true'
            )
        });

        jQuery('#' + seminar_id + '_' + cycle_id + '_hide').fadeOut('fast', function() {
            jQuery('#' + seminar_id + '_' + cycle_id + '_show').fadeIn('fast');
        });
    },

    /**
     * make a hidden seminar-entry visible in the schedule again
     *
     * @param  string  the seminar to be shown
     * @param  string  the cycle-id of the regular time-entry to be shown
     *                 (a seminar can have multiple of these
     */
    instSemBind: function(seminar_id, cycle_id) {
        Schedule.inst_changed = true;
        jQuery.ajax({
            type: 'GET',
            url: STUDIP.URLHelper.getURL(
                'dispatch.php/calendar/schedule/adminbind/' + seminar_id + '/' + cycle_id + '/1/true'
            )
        });

        jQuery('#' + seminar_id + '_' + cycle_id + '_show').fadeOut('fast', function() {
            jQuery('#' + seminar_id + '_' + cycle_id + '_hide').fadeIn('fast');
        });
    },

    /**
     * hide the popup of grouped-entry, containing a list of seminars.
     * returns true if the visiblity of one of the entries has been changed,
     * false otherwise
     *
     * @param  object  the element to be hidden
     *
     * @return  bool  true if the visibility of one seminar hase changed, false otherwise
     */
    hideInstOverlay: function(element) {
        if (Schedule.inst_changed) {
            return true;
        }
        jQuery(element).fadeOut('fast');

        Calendar.click_in_progress = false;

        return false;
    },

    /**
     * calls Calendar.checkTimeslot to check that the time is valid
     *
     * @param  bool  returns true if the time is valid, false otherwise
     */
    checkFormFields: function() {
        if (
            !Calendar.checkTimeslot(
                jQuery('#schedule_entry_hours > input[name=entry_start_hour]'),
                jQuery('#schedule_entry_hours > input[name=entry_start_minute]'),
                jQuery('#schedule_entry_hours > input[name=entry_end_hour]'),
                jQuery('#schedule_entry_hours > input[name=entry_end_minute]')
            )
        ) {
            jQuery('#schedule_entry_hours').addClass('invalid');
            jQuery('#schedule_entry_hours > span[class=invalid_message]').show();
            return false;
        }

        return true;
    }
};

export default Schedule;
