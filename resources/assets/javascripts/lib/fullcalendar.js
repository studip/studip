
/**
 * This class contains Stud.IP specific code for the fullcalendar package.
 */

import { Calendar } from '@fullcalendar/core';
import deLocale from '@fullcalendar/core/locales/de';
import enLocale from '@fullcalendar/core/locales/en-gb';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import resourceCommonPlugin from '@fullcalendar/resource-common';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';

// import '@fullcalendar/core/main.css';
// import '@fullcalendar/daygrid/main.css';
// import '@fullcalendar/timegrid/main.css';
// import '@fullcalendar/timeline/main.css';
// import '@fullcalendar/resource-timeline/main.css';


class Fullcalendar
{
    /**
     * The initialisation method. It loads the JS files for fullcalendar
     * in case they are not loaded and sets up a fullcalendar instance
     * for the nodes specified in the parameter node.
     *
     * @param DOMElement|string node The node which shall have a full calendar.
     *     This must either be a DOMElement or a string
     *     containing a CSS selector.
     */
    static init(node = '', fullcalendar_options = null)
    {
        if (!node) {
            //We need a CSS selector or a node!
            return;
        }

        //CSS code can be loaded "somewhen".
        import(/* webpackChunkName: "fullcalendar" */ '@fullcalendar/core/main.css');
        import(/* webpackChunkName: "fullcalendar" */ '@fullcalendar/daygrid/main.css');
        import(/* webpackChunkName: "fullcalendar" */ '@fullcalendar/timegrid/main.css');
        import(/* webpackChunkName: "fullcalendar" */ '@fullcalendar/timeline/main.css');
        import(/* webpackChunkName: "fullcalendar" */ '@fullcalendar/resource-timeline/main.css');

        var calendar = new Calendar(jQuery(node)[0], fullcalendar_options);
        node.calendar = calendar;
        calendar.render();
    }

    /**
     * Converts semester events to the default fullcalendar event format.
     * The begin and end date are converted to fit into the current week.
     */
    static convertSemesterEvents(event_data)
    {
        if (!event_data) {
            return {};
        }

        var calendar_start = sessionStorage.getItem('fullcalendar-defaultDate');

        var start = new String(event_data.start).split('T');
        var end = new String(event_data.end).split('T');

        //start and end must be transformed to the current week.
        //Therefore, we need the ISO weekdays for begin and end.
        var fake_start = new Date(calendar_start);
        fake_start.setHours(12);
        fake_start.setMinutes(0);
        fake_start.setSeconds(0);
        var fake_end = new Date(calendar_start);
        fake_end.setHours(12);
        fake_end.setMinutes(0);
        fake_end.setSeconds(0);

        //Calculcate the week day of the current week for the event
        //from the current day.
        var start_day_diff = fake_start.getDay();
        var end_day_diff = fake_end.getDay();
        //Convert sunday to ISO format:
        if (start_day_diff < 1) {
            start_day_diff = 7;
        }
        if (end_day_diff < 1) {
            end_day_diff = 7;
        }
        start_day_diff = start_day_diff - event_data.studip_weekday_begin;
        end_day_diff = end_day_diff - event_data.studip_weekday_end;

        var fake_start = new Date(
            fake_start.getTime() - (86400000 * start_day_diff)
        );
        var fake_end = new Date(
            fake_end.getTime() - (86400000 * end_day_diff)
        );

        //Output the modified begin and end date in the correct ISO format:
        event_data.start = fake_start.getFullYear() + '-'
            + (fake_start.getMonth() < 9 ? '0' : '') +
            (fake_start.getMonth() + 1) + '-'
            + (fake_start.getDate() < 10 ? '0' : '') + fake_start.getDate()
            + 'T' + start[1];
        event_data.end = fake_end.getFullYear() + '-'
            + (fake_end.getMonth() < 9 ? '0' : '') +
            + (fake_end.getMonth() + 1) + '-'
            + (fake_end.getDate() < 10 ? '0' : '') + fake_end.getDate()
            + 'T' + end[1];

        return event_data;
    }


    static createSemesterCalendarFromNode(node = null, additional_config = null)
    {
        if (!node) {
            //Ain't no fullcalendar when the node's gone!
            return;
        }

        var config = jQuery(node).data('config');

        config = jQuery.extend(
            config,
            additional_config || {},
        );

        if (Array.isArray(config.eventSources)) {
            var modified_event_sources = [];
            for (var s of config.eventSources) {
                if (s['url'] != undefined) {
                    //An URL source requires special treatment in the semester plan.
                    //Convert it to a function:
                    modified_event_sources.push(
                        jQuery.extend(
                            {
                                eventDataTransform: STUDIP.Fullcalendar.convertSemesterEvents
                            },
                            s
                        )
                    );
                } else {
                    modified_event_sources.push(s);
                }
            }
            config.eventSources = modified_event_sources;
        }

        /* config = jQuery.extend(
            {
                defaultView: 'dayGridWeek',
                columnHeaderFormat: {hour: '2-digit', minute: '2-digit'},
                header: {
                    left: '',
                    center: '',
                    right: ''
                }
            },
            additional_config || {}
        ); */

        return this.createFromNode(node, config);
    }


    static defaultResizeEventHandler(info)
    {
        if (!info.event.durationEditable || !info.view.viewSpec.options.editable) {
            //Read-only events cannot be resized!
            info.revert();
            return;
        }

        if (info.event.extendedProps.studip_api_urls['resize']) {
            jQuery.ajax(
                {
                    url: info.event.extendedProps.studip_api_urls['resize'],
                    method: 'POST',
                    async: false,
                    data: {
                        begin: this.toRFC3339String(info.event.start),
                        end: this.toRFC3339String(info.event.end)
                    }
                }
            ).fail(info.revert);
        }
    }

    static toRFC3339String(date)
    {

        var timezone_offset_min = date.getTimezoneOffset(),
            offset_hrs = parseInt(Math.abs(timezone_offset_min/60)),
            offset_min = Math.abs(timezone_offset_min%60),
            timezone_standard;

        if(offset_hrs < 10)
            offset_hrs = '0' + offset_hrs;

        if(offset_min < 10)
            offset_min = '0' + offset_min;

        // Add an opposite sign to the offset
        // If offset is 0, it means timezone is UTC
        if(timezone_offset_min < 0)
            timezone_standard = '+' + offset_hrs + ':' + offset_min;
        else if(timezone_offset_min > 0)
            timezone_standard = '-' + offset_hrs + ':' + offset_min;
        else if(timezone_offset_min == 0)
            timezone_standard = '+00:00';

        var current_date = date.getDate(),
            current_month = date.getMonth() + 1,
            current_year = date.getFullYear(),
            current_hrs = date.getHours(),
            current_mins = date.getMinutes(),
            current_secs = date.getSeconds(),
            current_datetime;

        // Add 0 before date, month, hrs, mins or secs if they are less than 0
        current_date = current_date < 10 ? '0' + current_date : current_date;
        current_month = current_month < 10 ? '0' + current_month : current_month;
        current_hrs = current_hrs < 10 ? '0' + current_hrs : current_hrs;
        current_mins = current_mins < 10 ? '0' + current_mins : current_mins;
        current_secs = current_secs < 10 ? '0' + current_secs : current_secs;

        // Current datetime
        // String such as 2016-07-16T19:20:30
        current_datetime = current_year + '-' + current_month + '-' + current_date + 'T' + current_hrs + ':' + current_mins + ':' + current_secs;

        return current_datetime + timezone_standard;
    }


    static defaultDropEventHandler(info)
    {
        //The logic from fullcalendar is inversed here:
        //If the calendar isn't editable, the event isn't either.
        if (!info.event.startEditable || !info.view.viewSpec.options.editable) {
            //Read-only events cannot be dragged and dropped!
            info.revert();
            return;
        }

        var drop_resource_id = info.newResource ? info.newResource.id : info.event.extendedProps.studip_range_id;

        if (info.event.extendedProps.studip_api_urls['move']) {
            if (info.event.allDay) {
                jQuery.ajax(
                    {
                        async: false,
                        url: info.event.extendedProps.studip_api_urls['move'],
                        method: 'POST',
                        data: {
                            resource_id: drop_resource_id,
                            begin: this.toRFC3339String(info.event.start.setHours(0,0,0)),
                            end: this.toRFC3339String(info.event.start.setHours(23,59,59))
                        }
                    }
                ).fail(info.revert);
            } else {
                if (info.event.end == null) {
                    var real_end = new Date();
                    real_end.setTime(info.event.start.getTime());
                    real_end.setHours(info.event.start.getHours()+2);
                    jQuery.ajax(
                        {
                            async: false,
                            url: info.event.extendedProps.studip_api_urls['move'],
                            method: 'POST',
                            data: {
                                resource_id: drop_resource_id,
                                begin: this.toRFC3339String(info.event.start),
                                end: this.toRFC3339String(real_end)
                            }
                        }
                    ).fail(info.revert);
                } else {
                    jQuery.ajax(
                        {
                            async: false,
                            url: info.event.extendedProps.studip_api_urls['move'],
                            method: 'POST',
                            data: {
                                resource_id: drop_resource_id,
                                begin: this.toRFC3339String(info.event.start),
                                end: this.toRFC3339String(info.event.end)
                            }
                        }
                    ).fail(info.revert);
                }
            }
        }
    }


    static createFromNode(node = null, additional_config = null)
    {
        if (!node) {
            //No node? No fullcalendar!
            return;
        }

        var config = jQuery(node).data('config');

        var storedDate = sessionStorage.getItem('fullcalendar-defaultDate');
        if (config['defaultDate'] === null && !(storedDate == undefined || storedDate == null )) {
            config['defaultDate'] = storedDate;
        }

        //Make sure the default values are set, if they are not found
        //in the additional_config object:
        config = jQuery.extend(
            {
                plugins: [ interactionPlugin, dayGridPlugin, timeGridPlugin, resourceCommonPlugin, resourceTimeGridPlugin, resourceTimelinePlugin ],
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                defaultView: 'timeGridWeek',
                header: {left: 'dayGridMonth,timeGridWeek,timeGridDay'},
                minTime: '08:00:00',
                maxTime: '20:00:00',
                height: 'auto',
                contentHeight: 'auto',
                firstDay: 1,
                weekNumberCalculation: 'ISO',
                locales: [ enLocale, deLocale ],
                locale:  (String.locale == 'de-DE')?'de':'en-gb',
                timeFormat: 'H:mm',
                nowIndicator: true,
                timezone: 'local',
                studip_functions: [],
                resourceAreaWidth: "20%",
                select: function(selectionInfo) {
                    if (!selectionInfo.view.viewSpec.options.editable || !selectionInfo.view.viewSpec.options.studip_urls) {
                        //The calendar isn't editable.
                        return;
                    }
                    if (selectionInfo.view.viewSpec.options.studip_urls['add']) {
                        if (selectionInfo.resource) {
                            STUDIP.Dialog.fromURL(
                                selectionInfo.view.viewSpec.options.studip_urls['add'],
                                {
                                    data: {
                                        begin: selectionInfo.start.getTime()/1000,
                                        end: selectionInfo.end.getTime()/1000,
                                        ressource_id: selectionInfo.resource.id
                                    }
                                }
                            )
                        } else {
                            STUDIP.Dialog.fromURL(
                                selectionInfo.view.viewSpec.options.studip_urls['add'],
                                {
                                    data: {
                                        begin: selectionInfo.start.getTime()/1000,
                                        end: selectionInfo.end.getTime()/1000
                                    }
                                }
                            )
                        }

                    }
                },
                eventClick: function(eventClickInfo) {
                    var event = eventClickInfo.event;
                    var extended_props = event.extendedProps;
                    if (extended_props.studip_view_urls == undefined) {
                        return;
                    }
                    if (!event.startEditable && extended_props.studip_view_urls['show']) {
                        STUDIP.Dialog.fromURL(
                            STUDIP.URLHelper.getURL(extended_props.studip_view_urls['show'])
                        );
                    } else if (event.startEditable && extended_props.studip_view_urls['edit']) {
                        STUDIP.Dialog.fromURL(
                            STUDIP.URLHelper.getURL(extended_props.studip_view_urls['edit'])
                        );
                    }
                    return false;
                },
                eventResize: function(info) {
                    //The logic from fullcalendar is inversed here:
                    //If the calendar isn't editable, the event isn't either.
                    if (info.view.viewSpec.options.studip_functions['resize_event']) {
                        var f = info.view.viewSpec.options.studip_functions['resize_event'];
                        f(info);
                    } else {
                        STUDIP.Fullcalendar.defaultResizeEventHandler(info);
                    }
                    info.event.source.refetch();
                },
                eventDrop: function(info) {
                    if (info.view.viewSpec.options.studip_functions['drop_event']) {
                        var f = info.view.viewSpec.options.studip_functions['drop_event'];
                        f(info);
                    } else {
                        STUDIP.Fullcalendar.defaultDropEventHandler(info);
                    }
                    info.event.source.refetch();
                },
                eventRender: function(info) {
                    var event = info.event;
                    var eventElement = info.el;
                    jQuery(eventElement).attr('title', event.title);
                    if (event.extendedProps.icon) {
                        var image = '<img src="' +
                                    STUDIP.ASSETS_URL +
                                    'images/icons/white/'+ event.extendedProps.icon +'.svg' +
                                    '" style="vertical-align:text-bottom;margin-right:3px;width:14px;height:14px"/>';
                                    jQuery(eventElement).find('.fc-title').prepend(jQuery(image));
                    }
                },
                loading: function(isLoading) {
                    if (isLoading) {
                        if (!jQuery('#loading-spinner').length) {
                            jQuery('.studip-fullcalendar-header').after(
                                jQuery('<div id="loading-spinner" style="text-align:center;">').html(
                                    jQuery('<img>')
                                        .attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg')
                                        .css('width', '64')
                                        .css('height', '64')
                                )
                            );
                        }
                    } else {
                        if (jQuery('#loading-spinner').length) {
                            jQuery('#loading-spinner').empty();
                        }
                        this.updateSize();
                    }
                },
                eventPositioned: function(info) {

                    if (jQuery(info.view.calendar.el).hasClass('individual-booking-plan')
                        || jQuery(info.view.calendar.el).hasClass('appointment-booking-plan')) {

                        jQuery('.fc-event').droppable(
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
                    }
                },
                datesRender: function (info) {

                    var view = info.view;
                    var start = view.activeStart;
                    var end = view.activeEnd;

                    if (start) {
                        var storableYear = start.getFullYear();
                        var storableMonth = (start.getMonth() + 1);
                        storableMonth = storableMonth<9 ? '0'+storableMonth : storableMonth;
                        var storableDay = start.getDate();
                        storableDay = storableDay<9 ? '0'+storableDay : storableDay;
                        sessionStorage.setItem('fullcalendar-defaultDate', storableYear + '-' + storableMonth + '-' + storableDay);
                    }

                    if (jQuery(".booking-plan-header").length) {
                        //Check if the booking plan header fields have already
                        //been moved below the fullcalendar header.
                        //If not, move them there.
                        if (!jQuery('.fc .booking-plan-header').length) {
                            //The header fields are not in their place.
                            jQuery('.booking-plan-header').insertAfter('.fc-toolbar');
                        }

                        end.setDate(end.getDate()-1);
                        var sem_start = jQuery(".booking-plan-header").data('semester-begin');
                        var sem_end = jQuery(".booking-plan-header").data('semester-end');

                        if (start.getTime()/1000 < sem_start || start.getTime()/1000 > sem_end) {
                            sem_start = null;
                            sem_end = null;
                        } else {
                            var sem_week = Math.floor((start.getTime()/1000 - sem_start) / 604800)+1;
                            jQuery("#booking-plan-header-semweek").text(sem_week);
                        }

                        Date.prototype.getWeekNumber = function(){
                            var d = new Date(Date.UTC(this.getFullYear(), this.getMonth(), this.getDate()));
                            var dayNum = d.getUTCDay() || 7;
                            d.setUTCDate(d.getUTCDate() + 4 - dayNum);
                            var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
                            return Math.ceil((((d - yearStart) / 86400000) + 1)/7)
                        };

                        jQuery("#booking-plan-header-calweek").text(start.getWeekNumber());
                        jQuery("#booking-plan-header-calbegin").text(start.toLocaleDateString('de-DE', {weekday: 'short'}) + ' ' + start.toLocaleDateString('de-DE'));
                        jQuery("#booking-plan-header-calend").text(end.toLocaleDateString('de-DE', {weekday: 'short'}) + ' ' + end.toLocaleDateString('de-DE'));

                        if (!sem_start || !sem_end) {
                            STUDIP.Resources.updateBookingPlanSemesterByView(view);
                        }
                    }
                }/* ,
                resourceRender: function(renderInfo) {
                    if ($(renderInfo.view.calendar.el).hasClass('room-group-booking-plan')) {
                        if ($(renderInfo.view.calendar.el).hasClass('semester-plan')) {
                            var url = STUDIP.URLHelper.getURL('dispatch.php/resources/room_planning/semester_plan/' + renderInfo.resource.id);
                        } else {
                            var url = STUDIP.URLHelper.getURL('dispatch.php/resources/room_planning/booking_plan/' + renderInfo.resource.id);
                        }
                        jQuery(renderInfo.el).find(".fc-cell-text").html(
                            jQuery('<a href="'+ url +'">')
                            .text(renderInfo.el.innerText)
                            .append(
                                jQuery('<img>')
                                .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/link-intern.svg')
                                .css('width', '16')
                                .css('height', '16')
                                .css('margin', '0px 5px')
                                .addClass('text-bottom')
                            )
                        );
                    }
                } */
            },
            config
        );

        config = jQuery.extend(
            config,
            additional_config || {},
        );

        return this.init(node,config);
    }
};


export default Fullcalendar;
