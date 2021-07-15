import {$gettext} from '../lib/gettext.js';

STUDIP.ready(function () {
    //Event definitions:
    jQuery(document).on(
        'change',
        '.room-search-form .criteria-selector',
        function (event) {
            STUDIP.Resources.addSearchCriteriaToRoomSearchWidget(
                event.target
            );
        }
    );

    jQuery(document).on(
        'click',
        '.room-search-form .criteria-list .remove-icon',
        function (event) {
            STUDIP.Resources.removeSearchCriteriaFromRoomSearchWidget(
                event.target
            );
        }
    );

    //permission list:

    jQuery(document).on(
        'click',
        '.resource-permission-list-action.apply-to-all-action',
        function (event) {
            var table = jQuery('#RoomGroupCommonPermissionTable')[0];
            var tr = jQuery(event.target).parents('tr')[0];
            if (!tr) {
                return;
            }
            var user_id = jQuery(tr).data('user_id');
            STUDIP.Resources.addUserToPermissionList(user_id, table);
            //Delete the row:
            jQuery(tr).remove();
        }
    );

    //Temporary permission list: Set the hidden checkbox to the state of the
    //proxy checkbox:
    jQuery(document).on('change', '#resource-temporary-permissions input.bulk-proxy', function () {
        var bulk_checked = jQuery(this).prop('checked');
        var bulk_indeterminate = jQuery(this).prop('indeterminate');
        if (bulk_checked || bulk_indeterminate) {
            jQuery('#resource-temporary-permissions input.bulk-datetime-enable').prop('checked', true);
        } else {
            jQuery('#resource-temporary-permissions input.bulk-datetime-enable').prop('checked', false);
        }
    });

    //Dialog for adding/editing bookings:

    if (jQuery('form.create-booking-form').length) {
        STUDIP.Resources.moveTimeOptions(jQuery('input[name="booking_style"]:checked').val());
    }

    //Set the date selector in the sidebar to the date from the session,
    //if that is set and no date is set in the URL.
    var date_set = false;
    var url_param_string = window.location.search;
    if (url_param_string) {
        var url_params = new URLSearchParams(url_param_string);
        if (url_params.get('defaultDate')) {
            date_set = true;
        }
    }
    if (!date_set) {
        var date_input = jQuery('#booking-plan-jmpdate')[0];
        if (date_input) {
            var session_date_string = sessionStorage.getItem('booking_plan_date');
            if (session_date_string) {
                //The date string is in the format YYYY-MM-DD and has to be
                //converted to the format DD.MM.YYYY.
                var date_parts = session_date_string.split('-');
                jQuery(date_input).val(date_parts[2] + '.' + date_parts[1] + '.' + date_parts[0]);
            }
        }
    }

    //other:

    jQuery(document).on(
        'click',
        '.booking-list-interval .takes-place-status-toggle',
        STUDIP.Resources.toggleBookingIntervalStatus
    );

    jQuery(document).on(
        'click',
        '.resource-category-properties-table .add-action',
        STUDIP.Resources.addResourcePropertyToTable
    );

    jQuery(document).on(
        'click',
        '.resource-request-properties-table .add-action',
        STUDIP.Resources.addPropertyToRequest
    );

    jQuery(document).on(
        'click',
        '.resource-category-properties-table .delete-action, .resource-request-properties-table .delete-action',
        function (event) {
            var row = jQuery(event.target).parents('tr')[0];
            if (!row) {
                return;
            }

            var property_id = jQuery(row).data('property_id');

            //Enable the property in the "add property" select element:
            var table = jQuery(row).parents('table')[0];
            if (!table) {
                return;
            }

            var select = jQuery(table).find('select.available-property-select')[0];
            if (!select) {
                select = jQuery(table).find('select.requestable-properties-select')[0];
                if (!select) {
                    return;
                }
            }

            var option = jQuery(select).find('option[value="' + property_id + '"]')[0];
            if (!option) {
                return;
            }
            jQuery(option).removeAttr('disabled');

            //As a final step: delete the row:
            jQuery(row).remove();
        }
    );

    //Event handlers for the individual booking plan print view:
    jQuery('.sidebar .colour-selector').draggable(
        {
            cursorAt: {
                left: 28, top: 15
            },
            appendTo: 'body',
            helper: function () {
                var dragged_item = jQuery(
                    '<div class="dragged-colour"></div>'
                );
                jQuery(dragged_item).css(
                    {
                        backgroundColor: jQuery(this).css('background-color'),
                        width: jQuery(this).css('width'),
                        height: jQuery(this).css('height'),
                        zIndex: 1000
                    }
                );
                return dragged_item;
            },
            revert: true
        }
    );

    jQuery(document).on(
        'click',
        '.colour-selector',
        function (event) {
            jQuery(event.target).children().click();
        }
    );

    jQuery(document).on(
        'change',
        '.colour-selector input[type="color"]',
        function (event) {
            jQuery(event.target).parent().css(
                'background-color',
                jQuery(event.target).val()
            );
        }
    );

    jQuery(document).on(
        'dragenter',
        '.individual-booking-plan .appointment-booking-plan .schedule_entry',
        function (event) {
            jQuery(event.target).css('opacity', '0.7');
        }
    );

    jQuery(document).on(
        'dragleave',
        '.individual-booking-plan .appointment-booking-plan .schedule_entry',
        function (event) {
            jQuery(event.target).css('opacity', '1.0');
        }
    );

    jQuery(document).on(
        'dragend',
        '.dragged-colour',
        function (event) {
            jQuery(event.target).css(
                {
                    'top': '0px',
                    'left': '0px'
                }
            );
        }
    );

    jQuery('.schedule_entry').droppable(
        {
            drop: function (event, ui_element) {
                event.preventDefault();

                var booking_plan_entry = event.target;
                var new_background_colour = jQuery(
                    ui_element.helper
                ).css('background-color');

                jQuery(booking_plan_entry).css(
                    'background-color',
                    new_background_colour
                );

                jQuery(booking_plan_entry).find('dl').css(
                    {
                        backgroundColor: new_background_colour,
                        borderColor: new_background_colour
                    }
                );
                jQuery(booking_plan_entry).find('dt').css(
                    'background-color',
                    new_background_colour
                );
            }
        }
    );

    //For the message functionality of the resource system:

    jQuery(document).on(
        'click',
        '.resources_messages-form .selection-area .remove-icon',
        function (event) {
            jQuery(event.target).parent().remove();
        }
    );

    //Handle the selection of room "sources":

    jQuery(document).on(
        'click',
        '.resources_messages-form input[name="room_selection"]',
        function (event) {
            //Hide the select field or the search field depending
            //on the room selection radio button:
            var room_selection = jQuery(event.target).val();
            if (room_selection == 'search') {
                jQuery(
                    '.resources_messages-form select[name="clipboard_id"]'
                ).attr('disabled', 'disabled');
                jQuery(
                    '.resources_messages-form input[name="room_name_parameter"]'
                ).removeAttr('disabled');
            } else {
                jQuery(
                    '.resources_messages-form input[name="room_name_parameter"]'
                ).attr('disabled', 'disabled');
                jQuery(
                    '.resources_messages-form select[name="clipboard_id"]'
                ).removeAttr('disabled');
            }
        }
    );

    //Handle the selection of recipient "sources":

    jQuery(document).on(
        'click',
        '.resources_messages-form input[name="recipient_selection"]',
        function (event) {
            var recipient_selection = jQuery(event.target).val();
            if (recipient_selection == 'permission') {
                jQuery('#RecipientMode_Booking').css('display', 'none');
                jQuery('#RecipientMode_Permission').css('display', 'block');
            } else {
                jQuery('#RecipientMode_Permission').css('display', 'none');
                jQuery('#RecipientMode_Booking').css('display', 'block');
            }
        }
    )

    //For the view resources/resource/assign:

    jQuery(document).on(
        'change',
        '.create-booking-form .booking-type-selection select',
        function (event) {
            if (jQuery(event.target).prop('tagName') != 'SELECT') {
                return;
            }
            var booking_type = jQuery(event.target).val();
            var form = jQuery(event.target).parents('form')[0];
            if (!form) {
                return;
            }

            //Activate the correct text for the separable rooms option:
            var separable_room_booking_spans = jQuery(form).find(
                'label.separable-room-booking span'
            );
            for (var span of separable_room_booking_spans) {
                var span_booking_type = jQuery(span).data('booking_type');
                if (span_booking_type == booking_type) {
                    jQuery(span).css('display', 'inline');
                } else {
                    jQuery(span).css('display', 'none');
                }
            }

            //Activate the correct legend for the comment fieldset:
            var comment_fieldset_legends = jQuery(form).find(
                'fieldset.comment-fieldset legend'
            );
            for (var legend of comment_fieldset_legends) {
                var legend_booking_type = jQuery(legend).data('booking_type');
                if (legend_booking_type == booking_type) {
                    jQuery(legend).css('display', 'block');
                } else {
                    jQuery(legend).css('display', 'none');
                }
            }

            //Activate the correct booking_type 2 elements:
            jQuery("*[data-booking_type='2']").each(function () {
                if (booking_type == '2') {
                    jQuery(this).show();
                } else {
                    jQuery(this).hide();
                }
            });
        }
    );

    jQuery(document).on(
        'change',
        'input[name="booking_style"]',
        function () {
            STUDIP.Resources.moveTimeOptions($(this).val());
        }
    );

    jQuery(document).on(
        'change',
        '.semester-time-option',
        function () {
            if (~$(this).attr('name').indexOf("begin")) {
                $("#BookingStartDateInput").prop("disabled", true);
            } else {
                $("#RepetitionEndInput").prop("disabled", true);
                $("#RepetitionEndInput").val($("input[name='semester_course_end_date']").val());
                $("#HiddenRepetitionEndInput").prop("disabled", false);
                $("#HiddenRepetitionEndInput").val($("input[name='semester_course_end_date']").val());
            }
            $(".semester-selector").parent().show();
        }
    );

    jQuery(document).on(
        'change',
        '.manual-time-option',
        function () {
            if (~$(this).attr('name').indexOf("begin")) {
                $("#BookingStartDateInput").prop("disabled", false);
            } else {
                $("#RepetitionEndInput").prop("disabled", false);
                $("#HiddenRepetitionEndInput").prop("disabled", true);
            }
            if (!$("#BookingStartDateInput").prop("disabled")
                && !$("#RepetitionEndInput").prop("disabled")) {
                $(".semester-selector").parent().hide();
            }
        }
    );

    jQuery(document).on(
        'change',
        '.manual-time-fields input[type="text"]',
        function () {
            var ds = $(this).val().split('.');
            var d = new Date(ds[1] + '/' + ds[0] + '/' + ds[2]);
            var day_numer = (d.getDay() || 7);

            if ($(this).attr('id') == 'BookingStartDateInput') {
                $("#begin_date-weekdays span").addClass('invisible');
                $("#begin_date-weekdays #" + day_numer).removeClass('invisible');
                var start_date_parts = jQuery(this).val().split('.');
                var repetition_end_date_parts = jQuery("#RepetitionEndInput").val().split('.');
                var start_date = new Date(
                    start_date_parts[2] + '-' + start_date_parts[1] + '-' + start_date_parts[0]
                        + 'T00:00:00'
                );
                var repetition_end_date = new Date(
                    repetition_end_date_parts[2] + '-' + repetition_end_date_parts[1] + '-'
                        + repetition_end_date_parts[0] + 'T00:00:00'
                );

                if (start_date > repetition_end_date
                    && $("input[name='selected_end']:checked").val() != 'semester_course_end') {
                    $("#RepetitionEndInput").prop('defaultValue', $(this).val());
                    $("#RepetitionEndInput").val($(this).val()).trigger('change');
                }

                if (!$('#multiday').prop('checked')
                    || $("#BookingEndDateInput").prop('defaultValue') ==
                    $("#BookingEndDateInput").val()) {
                    $("#BookingEndDateInput").prop('defaultValue', $(this).val());
                    $("#BookingEndDateInput").val($(this).val()).trigger('change');
                }
                updateRepeatEndSemesterByTimestamp(Math.floor(d / 1000));
            } else if ($(this).attr('id') == 'BookingEndDateInput') {
                $("#end_date-weekdays span").addClass('invisible');
                $("#end_date-weekdays #" + day_numer).removeClass('invisible');
            }
        }
    );

    jQuery(document).on(
        'change',
        'input[name="begin_date"]',
        function () {
            if (!$('#multiday').prop('checked')) {
                $('#end_date_section input').val($(this).val());
            }
        }
    );

    $(".new-clipboard-form #add-clipboard-button").removeAttr("disabled");
    var selected_clipboard_id = $('.clipboard-selector').val();
    $(".clipboard-area[data-id='" + selected_clipboard_id + "']").removeClass('invisible');
    if ($(".clipboard-area[data-id='" + selected_clipboard_id + "']").find(".empty-clipboard-message").hasClass("invisible")) {
        $("#clipboard-group-container").find('.widget-links').removeClass('invisible');
    }

    $('.special-item-switch').each(function () {
        if ($(this).prop('checked') == false) {
            $(this).next('label').children(':not(span)').hide();
        }
    });

    jQuery(document).on(
        'click',
        '.special-item-switch',
        function () {
            $(this).next('label').children(':not(span)').toggle();
        }
    );

    jQuery(document).on(
        'click',
        '#booking-plan-jmpdate-submit',
        function () {
            var picked = $('#booking-plan-jmpdate').val();
            var iso_date_string = '';
            if (picked.includes('.')) {
                var good_format = picked.split('.');
                var day = good_format[0];
                var month = good_format[1];
                var year = good_format[2];
                iso_date_string = year.padStart(4, "20") + '-' + month.padStart(2, "0") + '-' + day.padStart(2, "0");
            } else if (picked.includes('/')) {
                var bad_format = picked.split('/');
                var day = bad_format[1];
                var month = bad_format[0];
                var year = bad_format[2];
                iso_date_string = year.padStart(4, "20") + '-' + month.padStart(2, "0") + '-' + day.padStart(2, "0");
            } else if (picked.includes('-')) {
                iso_date_string = picked;
            }
            if (iso_date_string) {
                $('*[data-resources-fullcalendar="1"]').each(function () {
                    $(this)[0].calendar.gotoDate(iso_date_string);
                });
                updateDateURL();
            }
        }
    );

    jQuery(document).on(
        'change',
        'select[name="special__time_range_semester_id"]',
        function () {
            var selected_option = $(this).find('option:selected');
            if (selected_option) {
                var begin = new Date(parseInt(selected_option.attr('data-begin') + '000'));
                var end = new Date(parseInt(selected_option.attr('data-end') + '000'));
                $('input[name="special__time_range_begin_date"]').val(
                    $.datepicker.formatDate('dd.mm.yy', begin)
                );
                $('input[name="special__time_range_end_date"]').val(
                    $.datepicker.formatDate('dd.mm.yy', end)
                );
            }
        }
    );

    jQuery(document).on(
        'click',
        '.fc-button',
        function () {
            if ($(this).hasClass('fc-dayGridMonth-button')) {
                updateViewURL('dayGridMonth')
            } else if ($(this).hasClass('fc-timeGridWeek-button')) {
                updateViewURL('timeGridWeek')
            } else if ($(this).hasClass('fc-timeGridDay-button')) {
                updateViewURL('timeGridDay')
            } else if ($(this).hasClass('fc-today-button')
                || $(this).hasClass('fc-prev-button')
                || $(this).hasClass('fc-next-button')) {
                updateDateURL();
            }
        }
    );

    jQuery(document).on(
        'blur',
        '.hasDatepicker',
        function () {
            var new_val = $(this).val();
            switch ($(this).attr('name')) {
                case 'permissions[begin_date][]':
                case 'permissions[end_date][]':
                case 'bulk_begin_date':
                case 'bulk_end_date':
                    var now = new Date();
                    if (new_val.split('.').length === 1) {
                        $(this).val(new_val + '.' + ((now.getMonth() + 1) < 10 ? '0' + (now.getMonth() + 1) : (now.getMonth() + 1)) + '.' + now.getFullYear());
                    } else if (new_val.split('.').length === 2) {
                        $(this).val(new_val + '.' + now.getFullYear());
                    }
                    break;
                case 'permissions[begin_time][]':
                case 'permissions[end_time][]':
                case 'bulk_begin_time':
                case 'bulk_end_time':
                    if (new_val.split(':').length === 1) {
                        $(this).val(new_val + ':00');
                    }
                    break;
            }
        }
    );

    jQuery(document).on(
        'change blur',
        '#resource-temporary-permission-bulk-datetime input',
        function () {
            var targets = '';
            switch ($(this).attr('name')) {
                case 'bulk_begin_date':
                    targets = 'permissions[begin_date][]';
                    break;
                case 'bulk_begin_time':
                    targets = 'permissions[begin_time][]';
                    break;
                case 'bulk_end_date':
                    targets = 'permissions[end_date][]';
                    break;
                case 'bulk_end_time':
                    targets = 'permissions[end_time][]';
                    break;
            }
            var new_val = $(this).val();
            $('.resource-temporary-permission-row input[name="' + targets + '"]').each(function () {
                if ($(this).parents('tr').find('input[name="selected_permission_ids[]"]').prop('checked')) {
                    $(this).val(new_val);
                }
            });
        }
    );

    function updateRepeatEndSemesterByTimestamp(timestamp, api_url = 'api.php/semesters') {
        var semester = null;
        jQuery.ajax(
            STUDIP.URLHelper.getURL(api_url),
            {
                method: 'get',
                dataType: 'json',
                success: function (data) {
                    if (data) {
                        Object.values(data.collection).forEach(item => {
                            if (timestamp >= item.begin && timestamp < item.end) {
                                semester = item;
                            }
                        });
                        if (semester) {
                            $("#semester_course_name").text(semester.title);
                            $(".semester-time-option").prop('disabled', false);
                        } else {
                            if (data.pagination && data.pagination.links.next != api_url) {
                                semester = updateRepeatEndSemesterByTimestamp(timestamp, data.pagination.links.next);
                            } else {
                                $("#semester_course_name").text('außerhalb definierter Zeiten');
                                $(".semester-time-option").prop('checked', false);
                                $(".semester-time-option").prop('disabled', true);
                                $(".manual-time-option").prop('checked', true);
                                $(".manual-time-option").trigger('change');
                            }
                        }
                    }
                }
            }
        );
    }

    function updateViewURL(defaultView) {
        var sURLVariables = window.location.href.split(/[?&]/);
        var changed = false;
        for (var i = 0; i < sURLVariables.length; i++) {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == "defaultView") {
                sParameterName[1] = defaultView;
                sURLVariables[i] = sParameterName.join('=');
                changed = true;
            }
        }
        if (!changed) {
            sURLVariables.push('defaultView=' + defaultView);
        }
        if (sURLVariables.length > 2) {
            var newurl = sURLVariables[0] + '?' + sURLVariables[1] + '&';
            sURLVariables.shift();
            sURLVariables.shift();
            newurl += sURLVariables.join('&');
        } else {
            var newurl = sURLVariables.join('?');
        }
        history.pushState({}, null, newurl);
        var std_day = newurl.replace(/&?allday=\d+/, '');
        $('.booking-plan-std_view').attr('href', std_day);
        $('.booking-plan-allday_view').attr('href', std_day + '&allday=1');
    };

    function updateDateURL() {
        var changedmoment;
        $('*[data-resources-fullcalendar="1"]').each(function () {
            changedmoment = $(this)[0].calendar.getDate();
        });
        if (changedmoment) {
            //Get the timestamp:
            var timestamp = changedmoment.getTime() / 1000;

            //Set the URL parameter for the "export bookings" action
            //in the sidebar:
            var export_action = jQuery('#export-resource-bookings-action');
            if (export_action.length > 0) {
                var export_url = jQuery(export_action).attr('href');
                if (export_url.search(/[?&]timestamp=/) >= 0) {
                    export_url = export_url.replace(
                        /timestamp=[0-9]{0,10}/,
                        'timestamp=' + timestamp
                    );
                } else {
                    if (export_url.search(/\?/) >= 0) {
                        export_url += '&timestamp=' + timestamp;
                    } else {
                        export_url += '?timestamp=' + timestamp;
                    }
                }
                jQuery(export_action).attr('href', export_url);
            }

            //Now change the URL of the window.
            var changeddate = STUDIP.Fullcalendar.toRFC3339String(changedmoment).split('T')[0];
            var sURLVariables = window.location.href.split(/[?&]/);
            var changed = false;
            for (var i = 0; i < sURLVariables.length; i++) {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == "defaultDate") {
                    sParameterName[1] = changeddate;
                    sURLVariables[i] = sParameterName.join('=');
                    changed = true;
                }
            }
            if (!changed) {
                sURLVariables.push('defaultDate=' + changeddate);
            }
            if (sURLVariables.length > 2) {
                var newurl = sURLVariables[0] + '?' + sURLVariables[1] + '&';
                sURLVariables.shift();
                sURLVariables.shift();
                newurl += sURLVariables.join('&');
            } else {
                var newurl = sURLVariables.join('?');
            }
            history.pushState({}, null, newurl);
            var std_day = newurl.replace(/&?allday=\d+/, '');
            $('.booking-plan-std_view').attr('href', std_day);
            $('.booking-plan-allday_view').attr('href', std_day + '&allday=1');
            $('#booking-plan-jmpdate').val(changedmoment.toLocaleDateString('de-DE'));
            //Store the date in the sessionStorage:
            sessionStorage.setItem('booking_plan_date', changeddate)
        }
    };

    jQuery('#booking-plan-jmpdate').datepicker(
        {
            dateFormat: 'dd.mm.yy'
        }
    );
    jQuery('.resource-booking-time-fields input[type="date"]').datepicker(
        {
            dateFormat: 'yy-mm-dd'
        }
    );

    var nodes = jQuery('*.resource-plan[data-resources-fullcalendar="1"]');
    jQuery.each(nodes, function (index, node) {
        STUDIP.loadChunk('fullcalendar').then(() => {
            //Get the default date from the sessionStorage, if it is set
            //and no date is specified in the url.
            var use_session_date = true;
            var url_param_string = window.location.search;
            if (url_param_string) {
                var url_params = new URLSearchParams(url_param_string);
                if (url_params.get('defaultDate')) {
                    use_session_date = false;
                }
            }
            if (node.calendar == undefined) {
                if (jQuery(node).hasClass('semester-plan')) {
                    STUDIP.Fullcalendar.createSemesterCalendarFromNode(
                        node,
                        {
                            loading: function (isLoading) {
                                if (!isLoading) {
                                    var h = jQuery('section.studip-fullcalendar-header');
                                    if (h) {
                                        jQuery(h).removeClass('invisible');
                                        jQuery(h).insertAfter('.fc .fc-toolbar');
                                    }
                                }
                            }
                        }
                    );
                } else {
                    var config = {
                        studip_functions: {
                            drop_event:
                            STUDIP.Resources.dropEventInRoomGroupBookingPlan,
                            resize_event:
                            STUDIP.Resources.resizeEventInRoomGroupBookingPlan
                        },
                        loading: function (isLoading) {
                            if (!isLoading) {
                                var h = jQuery('section.studip-fullcalendar-header');
                                if (h) {
                                    jQuery(h).removeClass('invisible');
                                    jQuery(h).insertAfter('.fc .fc-toolbar');
                                }
                            }
                        }
                    };
                    if (use_session_date) {
                        var session_date_string = sessionStorage.getItem('booking_plan_date');
                        if (session_date_string) {
                            config.defaultDate = session_date_string;
                        }
                    }
                    STUDIP.Fullcalendar.createFromNode(node, config);
                }
            }
        });
    });

    //Check if an individual booking plan is to be displayed:
    var nodes = jQuery('.individual-booking-plan[data-resources-fullcalendar="1"]');
    jQuery.each(nodes, function (index, node) {
        STUDIP.loadChunk('fullcalendar').then(() => {
            STUDIP.Fullcalendar.createFromNode(
                node,
                {
                    eventPositioned: function (info, calendar_event, dom_element, view) {
                        var calendar_event = info.event;
                        var dom_element = info.el;
                        var view = info.view;
                        jQuery(dom_element).droppable({
                            drop: function (event, ui_element) {
                                event.preventDefault();

                                var booking_plan_entry = event.target;
                                var new_background_colour = jQuery(
                                    ui_element.helper
                                ).css('background-color');

                                jQuery(booking_plan_entry).css(
                                    'background-color',
                                    new_background_colour
                                );
                                jQuery(booking_plan_entry).css(
                                    'border-color',
                                    new_background_colour
                                );

                                jQuery(booking_plan_entry).find('dl').css({
                                    backgroundColor: new_background_colour,
                                    borderColor: new_background_colour
                                });
                                jQuery(booking_plan_entry).find('dt').css(
                                    'background-color',
                                    new_background_colour
                                );
                            }
                        });
                        var h = jQuery('section.studip-fullcalendar-header').clone();
                        if (h) {
                            jQuery(h).removeClass('invisible');
                            jQuery(h).insertAfter('.fc .fc-toolbar');
                        }
                    }
                }
            );
        });
    });

    jQuery(document).on(
        'click',
        '.create-booking-form .delete-assigned-user-icon',
        function (event) {
            var quicksearch = jQuery(event.target).parent().find('input');
            if (!quicksearch) {
                return;
            }
            jQuery(quicksearch).val('');
        }
    );

    jQuery(document).on(
        'click',
        '.request-list .request-marking-icon',
        function (event) {
            event.preventDefault();
            STUDIP.Resources.toggleRequestMarked(event.target);
        }
    );

    $(document).on(
        'click',
        "button[name='bulk-book-requests']",
        function (event) {
            STUDIP.Dialog.confirm(
                $gettext('Wollen Sie die im Plan gezeigten Anfragen wirklich buchen?')
            ).done(function () {
                STUDIP.Resources.bookAllCalendarRequests();
            });
        }
    );


    $(document).on('click', '.fc-request-event',
        function () {
            var parent_table_row = $(this).closest('tr');

            if($(parent_table_row).length) {
                $(parent_table_row).toggleClass('resource-planning-selected-request')
            }
            var objectData = $(this).data();
            var eventData = {
                id: objectData.eventId,
                title: objectData.eventTitle,
                start: objectData.eventBegin,
                end: objectData.eventEnd,
                studip_weekday_begin: objectData.eventStudip_weekday_begin,
                studip_weekday_end: objectData.eventStudip_weekday_end,
                request_id: objectData.eventRequest,
                tooltip: objectData.eventTooltip,
                studip_api_urls: {},
                studip_view_urls: {edit: objectData.eventView_urls_edit},
                editable: false,
                color: objectData.eventColor,
                textColor: '#000'
            };

            var calendarSektion = $('*[data-resources-fullcalendar="1"]')[0];
            if (calendarSektion) {
                var calendar = calendarSektion.calendar;
                if (calendar && eventData) {
                    var existingRequestEvent = calendar.getEventById(eventData.id);
                    if (existingRequestEvent) {
                        existingRequestEvent.remove();

                        var remainingRequestEvents = 0;
                        $('.fc-request-event').each(function () {
                            if (calendar.getEventById($(this).data().eventId)) {
                                remainingRequestEvents++;
                            }
                        });
                        if (remainingRequestEvents < 1) {
                            $("button[name='bulk-book-requests']").prop('disabled', true);
                        }
                    } else {
                        STUDIP.Fullcalendar.convertSemesterEvents(eventData, calendar.getDate().toString());
                        var overlap = false;
                        var checkStart = new Date(eventData.start);
                        var checkEnd = new Date(eventData.end);
                        $(calendar.getEvents()).each(function () {
                            // start-time in between any of the events
                            if ((checkStart >= this.start && checkStart < this.end)
                                //end-time in between any of the events
                                || (checkEnd > this.start && checkEnd <= this.end)
                                //any of the events in between/on the start-time and end-time
                                || (checkStart <= this.start && checkEnd >= this.end)) {
                                overlap = true
                            }
                        });
                        if (overlap) {
                            eventData.icon = 'exclaim-circle-full';
                        }
                        calendar.addEvent(eventData);
                        $("button[name='bulk-book-requests']").prop('disabled', false);
                    }

                }
            }
        }
    );
});


STUDIP.domReady(function() {
    jQuery(document).on(
        'click',
        '.room-clipboard-group-action',
        function (event) {
            //Get the IDs of the rooms of the clipboard:
            var active_clipboard = jQuery(event.target).parents("#clipboard-group-container").find(
                '.clipboard-area:not(.invisible)'
            )[0];
            if (!active_clipboard) {
                //Something is wrong with the HTML.
                return;
            }

            var clipboard_id = jQuery(active_clipboard).data('id');
            var action_needs_items = jQuery(event.target).data('needs_items');
            var show_in_dialog = jQuery(event.target).data('show_in_dialog');
            var ids = [];
            if (action_needs_items) {
                var items = jQuery(active_clipboard).find(
                    'tr.clipboard-item:not(.clipboard-item-template)'
                );

                for (var item of items) {
                    var input = jQuery(item).find("input[name='selected_clipboard_items[]']:checked")[0];
                    if (input) {
                        var id = jQuery(item).data('range_id');
                        //Check if id is an md5 sum:
                        if (id.match(/[0-9a-f]{32}/)) {
                            ids.push(id);
                        }
                    }
                }
                if (ids.length == items.length) {
                    //All items are selected. No need to use the Range-IDs, we
                    //can use the clipboard-ID instead.
                    action_needs_items = false;
                }
            }

            var url_path = jQuery(event.target).data('url_path');
            url_path = url_path.replace(/CLIPBOARD_ID/, clipboard_id);

            var complete_url = STUDIP.URLHelper.getURL(
                url_path,
                (
                    action_needs_items ? {'resource_ids': ids} : null
                )
            );

            if (show_in_dialog) {
                //If we have collected at least one ID we can create a dialog
                //displaying the comments of all the selected rooms:
                STUDIP.Dialog.fromURL(
                    complete_url,
                    {
                        size: 'normal'
                    }
                );
            } else {
                //Show the action in a new tab:
                window.open(complete_url, '_blank');
            }
            return false;
        }
    );
});
