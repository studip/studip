import { $gettext } from './lib/gettext.js';

/*jslint browser: true */
/*global jQuery, STUDIP */

/**
 * This file contains extensions/adjustments for jQuery UI.
 */

(function ($, STUDIP) {
    /**
     * Setup and refine date picker, add automated handling for .has-date-picker
     * and [data-date-picker].
     * Note: [date-datepicker] would be a way better selector but unfortunately
     * jQuery UI's Datepicker itself stores vital data in the the "datepicker"
     * data() variable, so we cannot use it and need to use "date-picker"
     * instead.
     *
     * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
     * @license GPL2 or any later version
     * @since   Stud.IP 3.4
     */

    'use strict';

    // Exit if datepicker is undefined (which it should never be)
    if ($.datepicker === undefined) {
        return;
    }

    // Exit if datetimepicker is undefined (which it should never be)
    if ($.ui.timepicker === undefined) {
        return;
    }

    // Setup defaults and default locales
    var defaults = {},
        locale = {
            closeText: $gettext('Schließen'),
            prevText: $gettext('Zurück'),
            nextText: $gettext('Vor'),
            currentText: $gettext('Jetzt'),
            monthNames: [
                $gettext('Januar'),
                $gettext('Februar'),
                $gettext('März'),
                $gettext('April'),
                $gettext('Mai'),
                $gettext('Juni'),
                $gettext('Juli'),
                $gettext('August'),
                $gettext('September'),
                $gettext('Oktober'),
                $gettext('November'),
                $gettext('Dezember')
            ],
            monthNamesShort: [
                $gettext('Jan'),
                $gettext('Feb'),
                $gettext('Mär'),
                $gettext('Apr'),
                $gettext('Mai'),
                $gettext('Jun'),
                $gettext('Jul'),
                $gettext('Aug'),
                $gettext('Sep'),
                $gettext('Okt'),
                $gettext('Nov'),
                $gettext('Dez')
            ],
            dayNames: [
                $gettext('Sonntag'),
                $gettext('Montag'),
                $gettext('Dienstag'),
                $gettext('Mittwoch'),
                $gettext('Donnerstag'),
                $gettext('Freitag'),
                $gettext('Samstag')
            ],
            dayNamesShort: [
                $gettext('So'),
                $gettext('Mo'),
                $gettext('Di'),
                $gettext('Mi'),
                $gettext('Do'),
                $gettext('Fr'),
                $gettext('Sa')
            ],
            weekHeader: $gettext('Wo'),
            dateFormat: 'dd.mm.yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: '',
            changeMonth: true,
            changeYear: true,
            timeOnlyTitle: $gettext('Zeit wählen'),
            timeText: $gettext('Zeit'),
            hourText: $gettext('Stunde'),
            minuteText: $gettext('Minute'),
            secondText: $gettext('Sekunde'),
            millisecText: $gettext('Millisekunde'),
            microsecText: $gettext('Mikrosekunde'),
            timezoneText: $gettext('Zeitzone'),
            timeFormat: $gettext('HH:mm'),
            amNames: [$gettext('vorm.'), 'AM', 'A'],
            pmNames: [$gettext('nachm.'), 'PM', 'P']
        };
    // Set dayNamesMin to dayNamesShort since they are equal
    locale.dayNamesMin = locale.dayNamesShort;

    // Setup Stud.IP's own datepicker extensions
    STUDIP.UI = STUDIP.UI || {};
    STUDIP.UI.Datepicker = {
        selector: '.has-date-picker,[data-date-picker]',
        // Initialize all datepickers that not yet been initialized (e.g. in dialogs)
        init: function () {
            $(this.selector).filter(function () {
                return $(this).data('date-picker-init') === undefined;
            }).each(function () {
                $(this).data('date-picker-init', true).datepicker();
            });
        },
        // Apply registered handlers. Take care: This happens upon before a
        // picker is shown as well as after a date has been selected.
        refresh: function () {
            $(this.selector).each(function () {
                var element = this,
                    options = $(element).data().datePicker;
                if (options) {
                    $.each(options, function (key, value) {
                        if (STUDIP.UI.Datepicker.dataHandlers.hasOwnProperty(key)) {
                            STUDIP.UI.Datepicker.dataHandlers[key].call(element, value);
                        }
                    });
                }
            });
        }
    };

    // Define handlers for any data-datepicker option
    STUDIP.UI.Datepicker.dataHandlers = {
        // Ensure this date is not later (<=) than another date by setting
        // the maximum allowed date the other date.
        // This will also set this date to the maximum allowed date if it
        // currently later than the allowed maximum date.
        '<=': function (selector, offset) {
            var this_date = $(this).datepicker('getDate'),
                max_date = null,
                temp,
                adjustment = 0;

            if ($(this).data().datePicker.offset) {
                temp = $(this).data().datePicker.offset;
                adjustment = parseInt($(temp).val(), 10);
            }

            // Get max date by either actual dates or maxDate options on
            // all matching elements
            if (selector === 'today') {
                max_date = new Date();
            } else {
                $(selector).each(function () {
                    var date = $(this).datepicker('getDate') || $(this).datepicker('option', 'maxDate');
                    if (date && (!max_date || date < max_date)) {
                        max_date = new Date(date);
                    }
                });
            }

            // Set max date and adjust current date if neccessary
            if (max_date) {
                max_date.setTime(max_date.getTime() - (offset || 0) * 24 * 60 * 60 * 1000);

                temp = new Date(max_date);
                temp.setDate(temp.getDate() - adjustment);

                if (this_date && this_date > max_date) {
                    $(this).datepicker('setDate', temp);
                }

                $(this).datepicker('option', 'maxDate', max_date);
            } else {
                $(this).datepicker('option', 'maxDate', null);
            }
        },
        // Ensure this date is earlier (<) than another date by setting the
        // maximum allowed date to the other date - 1 day.
        // This will also set this date to the maximum allowed date - 1 day
        // if it is currently later than the allowed maximum date.
        '<': function (selector) {
            STUDIP.UI.Datepicker.dataHandlers['<='].call(this, selector, 1);
        },
        // Ensure this date is not earlier (>=) than another date by setting
        // the minimum allowed date to the other date.
        // This will also set this date to the minimum allowed date if it is
        // currently earlier than the allowed minimum date.
        '>=': function (selector, offset) {
            var this_date = $(this).datepicker('getDate'),
                min_date = null,
                temp,
                adjustment = 0;

            if ($(this).data().datePicker.offset) {
                temp = $(this).data().datePicker.offset;
                adjustment = parseInt($(temp).val(), 10);
            }

            // Get min date by either actual dates or minDate options on
            // all matching elements
            if (selector === 'today') {
                min_date = new Date();
            } else {
                $(selector).each(function () {
                    var date = $(this).datepicker('getDate') || $(this).datepicker('option', 'minDate');
                    if (date && (!min_date || date > min_date)) {
                        min_date = new Date(date);
                    }
                });
            }

            // Set min date and adjust current date if neccessary
            if (min_date) {
                min_date.setTime(min_date.getTime() + (offset || 0) * 24 * 60 * 60 * 1000);

                temp = new Date(min_date);
                temp.setDate(temp.getDate() + adjustment);

                if (this_date && this_date < min_date) {
                    $(this).datepicker('setDate', temp);
                }

                $(this).datepicker('option', 'minDate', min_date);
            } else {
                $(this).datepicker('option', 'minDate', null);
            }
        },
        // Ensure this date is later (>) than another date by setting the
        // minimum allowed date to the other date + 1 day.
        // This will also set this date to the minimum allowed date + 1 day
        // if it is currently earlier than the allowed minimum date.
        '>': function (selector) {
            STUDIP.UI.Datepicker.dataHandlers['>='].call(this, selector, 1);
        }
    };

    STUDIP.UI.DateTimepicker = {
        selector: '.has-datetime-picker,[data-datetime-picker]',
        // Initialize all datetimepickers that not yet been initialized (e.g. in dialogs)
        init: function () {
            $(this.selector).filter(function () {
                return $(this).data('datetime-picker-init') === undefined;
            }).each(function () {
                $(this).data('datetime-picker-init', true).datetimepicker();
            });
        },
        // Apply registered handlers. Take care: This happens upon before a
        // picker is shown as well as after a date has been selected.
        refresh: function () {
            $(this.selector).each(function () {
                var element = this,
                    options = $(element).data().datetimePicker;
                if (options) {
                    $.each(options, function (key, value) {
                        if (STUDIP.UI.DateTimepicker.dataHandlers.hasOwnProperty(key)) {
                            STUDIP.UI.DateTimepicker.dataHandlers[key].call(element, value);
                        }
                    });
                }
            });
        }
    };

    // Define handlers for any data-datepicker option
    STUDIP.UI.DateTimepicker.dataHandlers = {
        // Ensure this date is not later (<=) than another date by setting
        // the maximum allowed date the other date.
        // This will also set this date to the maximum allowed date if it
        // currently later than the allowed maximum date.
        '<=': function (selector, offset) {
            var this_date = $(this).datetimepicker('getDate'),
                max_date = null,
                temp;

            if ((offset === undefined) && $(selector).data('offset')) {
                temp   = $(selector).data('offset');
                offset = parseInt($(temp).val(), 10);
            }

            // Get max date by either actual dates or maxDate options on
            // all matching elements
            if (selector === 'today') {
                max_date = new Date();
                max_date.setHours(0, 23, 59, 59);
            } else {
                $(selector).each(function () {
                    var date = $(this).datetimepicker('getDate') || $(this).datetimepicker('option', 'maxDate');
                    if (date && (!max_date || date < max_date)) {
                        max_date = new Date(date);
                    }
                });
            }

            // Set max date and adjust current date if neccessary
            if (max_date) {
                max_date.setTime(max_date.getTime() - (offset || 0) * 24 * 60 * 60 * 1000);

                if (this_date && this_date > max_date) {
                    $(this).datetimepicker('setDate', max_date);
                }

                $(this).datetimepicker('option', 'maxDate', max_date);
            } else {
                $(this).datetimepicker('option', 'maxDate', null);
            }
        },
        // Ensure this date is earlier (<) than another date by setting the
        // maximum allowed date to the other date - 1 day.
        // This will also set this date to the maximum allowed date - 1 day
        // if it is currently later than the allowed maximum date.
        '<': function (selector) {
            STUDIP.UI.DateTimepicker.dataHandlers['<='].call(this, selector, 1);
        },
        // Ensure this date is not earlier (>=) than another date by setting
        // the minimum allowed date to the other date.
        // This will also set this date to the minimum allowed date if it is
        // currently earlier than the allowed minimum date.
        '>=': function (selector, offset) {
            var this_date = $(this).datetimepicker('getDate'),
                min_date = null,
                temp;

            if ((offset === undefined) && $(selector).data('offset')) {
                temp   = $(selector).data('offset');
                offset = parseInt($(temp).val(), 10);
            }

            // Get min date by either actual dates or minDate options on
            // all matching elements
            if (selector === 'today') {
                min_date = new Date();
                min_date.setHours(0, 0, 0);
            } else {
                $(selector).each(function () {
                    var date = $(this).datetimepicker('getDate') || $(this).datetimepicker('option', 'minDate');
                    if (date && (!min_date || date > min_date)) {
                        min_date = new Date(date);
                    }
                });
            }

            // Set min date and adjust current date if neccessary
            if (min_date) {
                min_date.setTime(min_date.getTime() + (offset || 0) * 24 * 60 * 60 * 1000);

                if (this_date && this_date < min_date) {
                    $(this).datetimepicker('setDate', min_date);
                }

                $(this).datetimepicker('option', 'minDate', min_date);
            } else {
                $(this).datetimepicker('option', 'minDate', null);
            }
        },
        // Ensure this date is later (>) than another date by setting the
        // minimum allowed date to the other date + 1 day.
        // This will also set this date to the minimum allowed date + 1 day
        // if it is currently earlier than the allowed minimum date.
        '>': function (selector) {
            STUDIP.UI.DateTimepicker.dataHandlers['>='].call(this, selector, 1);
        }
    };

    STUDIP.UI.Timepicker = {
        selector: '.has-time-picker,[data-time-picker]',
        // Initialize all datetimepickers that not yet been initialized (e.g. in dialogs)
        init: function () {
            $(this.selector).filter(function () {
                return $(this).data('time-picker-init') === undefined;
            }).each(function () {
                $(this).addClass('hasTimepicker').data('time-picker-init', true).timepicker();
            });
        },
        // Apply registered handlers. Take care: This happens upon before a
        // picker is shown as well as after a date has been selected.
        refresh: function () {
            $(this.selector).each(function () {
                var element = this,
                    options = $(element).data().timePicker;
                if (options) {
                    $.each(options, function (key, value) {
                        if (STUDIP.UI.Timepicker.dataHandlers.hasOwnProperty(key)) {
                            STUDIP.UI.Timepicker.dataHandlers[key].call(element, value);
                        }
                    });
                }
            });
        }
    };

    STUDIP.UI.Timepicker.parseTime = (time) => {
        const split = time.split(':');
        return {
            hour: parseInt(split[0], 10),
            minute: parseInt(split[1], 10)
        };
    };
    STUDIP.UI.Timepicker.createTime = (hours, minutes) => {
        return ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2);
    };
    STUDIP.UI.Timepicker.setTime = (time) => {
        const date = new Date();
        const parsed = STUDIP.UI.Timepicker.parseTime(time);

        date.setHours(parsed.hour);
        date.setMinutes(parsed.minute);

        return date;
    };

    // Define handlers for any data-time-picker option
    // TODO: This don't work well if at all. We should probably switch to
    // another (date) timepicker
    STUDIP.UI.Timepicker.dataHandlers = {
        // Ensure this time is not later (<=) than another time by setting
        // the maximum allowed time on the other time.
        // This will also set this time to the maximum allowed time if it is
        // currently later than the allowed maximum time.
        '<=': function (selector, offset) {
            var this_time = this.value;
            var max_time = null;
            var temp;

            if ((offset === undefined) && $(selector).data('offset')) {
                temp   = $(selector).data('offset');
                offset = parseInt($(temp).val(), 10);
            }

            // Get max time by either actual times
            $(selector).each(function () {
                var time = this.value;
                if (time && (!max_time || time < max_time)) {
                    max_time = time;
                }
            });

            // Set max time and adjust current time if neccessary
            if (max_time) {
                const parsed = STUDIP.UI.Timepicker.parseTime(max_time);
                max_time = STUDIP.UI.Timepicker.createTime(
                    Math.min(23, Math.max(0, parsed.hour - (offset || 0))),
                    parsed.minute
                );

                console.log('max time:', this_time, max_time);
                if (this_time && this_time > max_time) {
                    $(this).timepicker(STUDIP.UI.Timepicker.parseTime(max_time));
                }

                $(this).timepicker({
                    maxTime: STUDIP.UI.Timepicker.setTime(max_time)
                });
            } else {
                $(this).timepicker({
                    maxTime: null
                });
            }
        },
        // Ensure this date is earlier (<) than another date by setting the
        // maximum allowed date to the other date - 1 day.
        // This will also set this date to the maximum allowed date - 1 day
        // if it is currently later than the allowed maximum date.
        '<': function (selector) {
            STUDIP.UI.Timepicker.dataHandlers['<='].call(this, selector, 1);
        },
        // Ensure this date is not earlier (>=) than another date by setting
        // the minimum allowed date to the other date.
        // This will also set this date to the minimum allowed date if it is
        // currently earlier than the allowed minimum date.
        '>=': function (selector, offset) {
            var this_time = this.value;
            var min_time = null;
            var temp;

            if ((offset === undefined) && $(selector).data('offset')) {
                temp   = $(selector).data('offset');
                offset = parseInt($(temp).val(), 10);
            }

            // Get min time by either actual times
            $(selector).each(function () {
                var time = this.value;
                if (time && (!min_time || time > min_time)) {
                    min_time = time;
                }
            });

            // Set min time and adjust current time if neccessary
            if (min_time) {
                const parsed = STUDIP.UI.Timepicker.parseTime(min_time);
                min_time = STUDIP.UI.Timepicker.createTime(
                    Math.min(23, Math.max(0, parsed.hour + (offset || 0))),
                    parsed.minute
                );

                console.log('min time:', this_time, min_time);
                if (this_time && this_time < min_time) {
                    $(this).timepicker(STUDIP.UI.Timepicker.parseTime(min_time));
                }

                $(this).timepicker({
                    minTime: STUDIP.UI.Timepicker.setTime(min_time)
                });
            } else {
                $(this).timepicker({
                    minTime: null
                });
            }
        },
        // Ensure this date is later (>) than another date by setting the
        // minimum allowed date to the other date + 1 day.
        // This will also set this date to the minimum allowed date + 1 day
        // if it is currently earlier than the allowed minimum date.
        '>': function (selector) {
            STUDIP.UI.Timepicker.dataHandlers['>='].call(this, selector, 1);
        }
    };

    // Apply defaults including date picker handlers
    defaults = Object.assign({}, locale, {
        beforeShow (input) {
            STUDIP.UI.Datepicker.refresh();
            STUDIP.UI.DateTimepicker.refresh();
            STUDIP.UI.Timepicker.refresh();

            if ($(input).parents('.ui-dialog').length > 0) {
                return;
            }

            $(input).css({
                'position': 'relative',
                'z-index': 1002
            });
        },
        onSelect: function (value, instance) {
            if (value !== instance.lastVal) {
                $(this).change();
            }
        }
    });

    $.datepicker.setDefaults(Object.assign({}, defaults, {
        beforeShow (input) {
            // Don't lose original behaviour
            defaults.beforeShow(input);

            if ($(input).parents('.ui-dialog').length > 0) {
                $('.ui-dialog-content').bind('scroll.datepicker-scroll', _.debounce($.proxy(DpHideOnScroll, null, input), 100, {leading:true, trailing:false}));
            }
            $(window).bind('scroll.datepicker-scroll', _.debounce($.proxy(DpHideOnScroll, null, input), 100, {leading:true, trailing:false}));

            if ($(input).closest('.sidebar').length === 0) {
                return;
            }

            const button = input.nextElementSibling;
            if (button && button.matches('input[type="submit"]')) {
                button.style.position = 'relative';
                button.style.zIndex = input.style.zIndex;
            }
        },
        onClose (date, inst) {
            $(this).one('click.picker', function () {
                $(this).datepicker('show');
            }).on('blur', function () {
                $(this).off('click.picker');
            });

            if ($(this).parents('.ui-dialog').length > 0) {
                $('.ui-dialog-content').unbind('scroll.datepicker-scroll');
            } else {
                $(window).unbind('scroll.datepicker-scroll');
            }
        }
    }));

    var DpHideOnScroll = function () {
        var input = arguments[0];
        $(input).blur();
        $(input).datepicker('hide');
    }

    $.timepicker.setDefaults(Object.assign({}, defaults, {
        timeFormat: 'HH:mm'
    }));

    // Attach global focus handler on date picker elements
    $(document).on('focus', STUDIP.UI.Datepicker.selector, () => {
        STUDIP.UI.Datepicker.init();
    });

    // Attach global focus handler on datetime picker elements
    $(document).on('focus', STUDIP.UI.DateTimepicker.selector, () => {
        STUDIP.UI.DateTimepicker.init();
    });

    // Attach global focus handler on time picker elements
    $(document).on('focus', STUDIP.UI.Timepicker.selector, (event) => {
        if (!$(event.target).attr('pattern')) {
            $(event.target).attr('pattern', '^[012]\\d:[0-5]\\d$');
        }

        STUDIP.UI.Timepicker.init();
    }).on('keyup', STUDIP.UI.Timepicker.selector, (event) => {
        const input = event.target;
        input.value = input.value.replace(/:+$/, ':');

        const value = input.value.trim();
        if (value.length === 2 && event.which !== 8) {
            input.value += ':';
        }
    }).on('blur', STUDIP.UI.Timepicker.selector, (event) => {
        if (event.target.checkValidity()) {
            return;
        }

        const input = event.target;
        let value = input.value.trim();
        value = value.replace(/:/g, '');
        if (['0', '1', '2'].indexOf(value.substr(0, 1)) === -1) {
            value = '0' + value;
        }
        value = (value + '00').substr(0, 4);

        input.value = value.substr(0, 2) + ':' + value.substr(2, 2);
    });

}(jQuery, STUDIP));
