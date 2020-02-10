STUDIP.ready(function() {
    //Event definitions:
    jQuery(document).on(
        'change',
        '.room-search-form .criteria-selector',
        function(event) {
            STUDIP.Resources.addSearchCriteriaToRoomSearchWidget(
                event.target
            );
        }
    );

    jQuery(document).on(
        'click',
        '.room-search-form .criteria-list .remove-icon',
        function(event) {
            STUDIP.Resources.removeSearchCriteriaFromRoomSearchWidget(
                event.target
            );
        }
    );

    //permission list:

    jQuery(document).on(
        'click',
        '.resource-permission-list-action.apply-to-all-action',
        function(event) {
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

    //Temporary permission list:

    jQuery('input[name="selected_permission_ids[]"]').click(function(){
        var any_checked = false;
        jQuery('input[name="selected_permission_ids[]"]').each(function(){
            if ($(this).prop('checked')) {
                any_checked = true;
                return false;
            }
        });
        if (any_checked) {
            $('#resource-temporary-permission-bulk-datetime').show();
        } else {
            $('#resource-temporary-permission-bulk-datetime').hide();
        }
    });

    jQuery('#TemporaryPermissionList th input[type="checkbox"], #TemporaryPermissionList input.bulk-proxy').click(function(){
        if ($(this).prop('checked')) {
            $('#resource-temporary-permission-bulk-datetime').show();
        } else {
            $('#resource-temporary-permission-bulk-datetime').hide();
        }
    });

    //Dialog for adding/editing bookings:

    if (jQuery('form.create-booking-form').length) {
        STUDIP.Resources.moveTimeOptions(jQuery('input[name="booking_style"]:checked').val());
    }

    //other:

    jQuery(document).on(
        'click',
        '.booking-list-interval .takes-place-status-toggle',
        STUDIP.Resources.toggleBookingIntervalStatus
    );

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

                for (item of items) {
                    var id = jQuery(item).data('range_id');
                    //Check if id is an md5 sum:
                    if (id.match(/[0-9a-f]{32}/)) {
                        ids.push(id);
                    }
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
        function(event) {
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
            drop: function(event, ui_element) {
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
            jQuery("*[data-booking_type='2']").each(function(){
                if (booking_type == '2') {
                    jQuery(this).show();
                }else {
                    jQuery(this).hide();
                }
            });
        }
    );

    jQuery(document).on(
        'change',
        'input[name="booking_style"]',
        function() {
            STUDIP.Resources.moveTimeOptions($(this).val());
        }
    );

    jQuery(document).on(
        'change',
        '.semester-time-option',
        function () {
            if (~$(this).attr('name').indexOf("begin")) {
                $("#BookingStartDateInput").prop( "disabled", true );
            } else {
                $("#RepetitionEndInput").prop( "disabled", true );
                $("#RepetitionEndInput").val($("input[name='semester_course_end_date']").val());
                $("#HiddenRepetitionEndInput").prop( "disabled", false );
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
                && !$("#RepetitionEndInput").prop("disabled"))
            {
                $(".semester-selector").parent().hide();
            }
        }
    );

    jQuery(document).on(
        'change',
        '.manual-time-fields input[type="text"]',
        function () {
            var ds = $(this).val().split('.');
            var d = new Date(ds[1]+'/'+ds[0]+'/'+ds[2]);
            if ($(this).attr('id') == 'BookingStartDateInput') {
                $("#begin_date-weekdays span").addClass('invisible');
                $("#begin_date-weekdays #"+d.getDay()).removeClass('invisible');

                if ($("#RepetitionEndInput").prop('defaultValue') ==
                    $("#RepetitionEndInput").val())
                {
                    $("#RepetitionEndInput").prop('defaultValue',$(this).val());
                    $("#RepetitionEndInput").val($(this).val()).trigger('change');
                }

                if (!$('#multiday').prop('checked')
                    || $("#BookingEndDateInput").prop('defaultValue') ==
                    $("#BookingEndDateInput").val())
                {
                    $("#BookingEndDateInput").prop('defaultValue',$(this).val());
                    $("#BookingEndDateInput").val($(this).val()).trigger('change');
                }
                updateRepeatEndSemesterByTimestamp(Math.floor(d / 1000));
            } else if ($(this).attr('id') == 'BookingEndDateInput') {
                $("#end_date-weekdays span").addClass('invisible');
                $("#end_date-weekdays #"+d.getDay()).removeClass('invisible');
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
    $(".clipboard-area[data-id='"+selected_clipboard_id+"']").removeClass('invisible');
    if ($(".clipboard-area[data-id='"+selected_clipboard_id+"']").find(".empty-clipboard-message").hasClass("invisible")) {
        $("#clipboard-group-container").find('.widget-links').removeClass('invisible');
    }

    $('.special-item-switch').each(function(){
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
        'change',
        '#booking-plan-jmpdate',
        function () {
            var picked = $(this).val();
            $('*[data-fullcalendar="1"]').each(function() {
                $(this)[0].calendar.gotoDate(picked.split('.').reverse().join('-'));
            });
            updateDateURL();
        }
    );

    jQuery(document).on(
        'change',
        'select[name="special__time_range_semester_id"]',
        function () {
            var selected_option = $(this).find('option:selected');
            if (selected_option) {
                var begin = new Date(parseInt(selected_option.attr('data-begin')+'000'));
                var end = new Date(parseInt(selected_option.attr('data-end')+'000'));
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
                if (new_val.split('.').length == 1) {
                    $(this).val(new_val + '.' + ((now.getMonth()+1) < 10 ? '0' + (now.getMonth()+1) : (now.getMonth()+1)) + '.' + now.getFullYear());
                } else if (new_val.split('.').length == 2) {
                    $(this).val(new_val + '.' + now.getFullYear());
                }
                break;
            case 'permissions[begin_time][]':
            case 'permissions[end_time][]':
            case 'bulk_begin_time':
            case 'bulk_end_time':
                if (new_val.split(':').length == 1) {
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
            $('.resource-temporary-permission-row input[name="'+targets+'"]').each(function(){
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
                success: function(data) {
                    if (data) {
                        for (element in data.collection) {
                            var sem = data.collection[element];
                            if (timestamp >= sem.begin && timestamp < sem.end) {
                                semester = sem;
                                break;
                            }
                        };
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
        $('*[data-fullcalendar="1"]').each(function() {
            changedmoment = $(this)[0].calendar.getDate();
        });
        if (changedmoment) {
            //Get the timestamp:
            var timestamp = changedmoment.getTime()/1000;

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
            changeddate = STUDIP.Fullcalendar.toRFC3339String(changedmoment).split('T')[0];
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
            if (node.calendar == undefined) {
                if (jQuery(node).hasClass('semester-plan')) {
                    STUDIP.Fullcalendar.createSemesterCalendarFromNode(
                        node,
                        {
                            loading: function(isLoading) {
                                if(!isLoading) {
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
                    STUDIP.Fullcalendar.createFromNode(
                        node,
                        {
                            studip_functions: {
                                drop_event:
                                STUDIP.Resources.dropEventInRoomGroupBookingPlan,
                                resize_event:
                                STUDIP.Resources.resizeEventInRoomGroupBookingPlan
                            },
                            loading: function(isLoading) {
                                if(!isLoading) {
                                    var h = jQuery('section.studip-fullcalendar-header');
                                    if (h) {
                                        jQuery(h).removeClass('invisible');
                                        jQuery(h).insertAfter('.fc .fc-toolbar');
                                    }
                                }
                            }
                        }
                    );
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
                            drop: function(event, ui_element) {
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

    //resources/room_request/resolve action:

    jQuery('#resolve-dates-table input[type="radio"]').on('click', function() {
        if ($(this).data('proxyfor')) {
            var newstate = this.previous?false:true;
            this.checked = newstate;
            $($(this).data('proxyfor')).each(function(){
                this.checked = newstate;
            })
            this.previous = this.checked;
        } else {
            if (this.previous) {
                this.checked = false;
            }
            this.previous = this.checked;
        }
    });
});
