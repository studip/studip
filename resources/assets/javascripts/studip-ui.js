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

    // Setup defaults and default locales (localizable by Stud.IP's JS
    // localization method through String.toLocaleString())
    var defaults = {},
        locale = {
            closeText: 'Schließen'.toLocaleString(),
            prevText: 'Zurück'.toLocaleString(),
            nextText: 'Vor'.toLocaleString(),
            currentText: 'Jetzt'.toLocaleString(),
            monthNames: [
                'Januar'.toLocaleString(),
                'Februar'.toLocaleString(),
                'März'.toLocaleString(),
                'April'.toLocaleString(),
                'Mai'.toLocaleString(),
                'Juni'.toLocaleString(),
                'Juli'.toLocaleString(),
                'August'.toLocaleString(),
                'September'.toLocaleString(),
                'Oktober'.toLocaleString(),
                'November'.toLocaleString(),
                'Dezember'.toLocaleString()
            ],
            monthNamesShort: [
                'Jan'.toLocaleString(),
                'Feb'.toLocaleString(),
                'Mär'.toLocaleString(),
                'Apr'.toLocaleString(),
                'Mai'.toLocaleString(),
                'Jun'.toLocaleString(),
                'Jul'.toLocaleString(),
                'Aug'.toLocaleString(),
                'Sep'.toLocaleString(),
                'Okt'.toLocaleString(),
                'Nov'.toLocaleString(),
                'Dez'.toLocaleString()
            ],
            dayNames: [
                'Sonntag'.toLocaleString(),
                'Montag'.toLocaleString(),
                'Dienstag'.toLocaleString(),
                'Mittwoch'.toLocaleString(),
                'Donnerstag'.toLocaleString(),
                'Freitag'.toLocaleString(),
                'Samstag'.toLocaleString()
            ],
            dayNamesShort: [
                'So'.toLocaleString(),
                'Mo'.toLocaleString(),
                'Di'.toLocaleString(),
                'Mi'.toLocaleString(),
                'Do'.toLocaleString(),
                'Fr'.toLocaleString(),
                'Sa'.toLocaleString()
            ],
            weekHeader: 'Wo',
            dateFormat: 'dd.mm.yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: '',
            changeMonth: true,
            changeYear: true,
            timeOnlyTitle: 'Zeit wählen'.toLocaleString(),
            timeText: 'Zeit'.toLocaleString(),
            hourText: 'Stunde'.toLocaleString(),
            minuteText: 'Minute'.toLocaleString(),
            secondText: 'Sekunde'.toLocaleString(),
            millisecText: 'Millisekunde'.toLocaleString(),
            microsecText: 'Mikrosekunde'.toLocaleString(),
            timezoneText: 'Zeitzone'.toLocaleString(),
            timeFormat: 'HH:mm'.toLocaleString(),
            amNames: ['vorm.'.toLocaleString(), 'AM', 'A'],
            pmNames: ['nachm.'.toLocaleString(), 'PM', 'P']
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

    // Define handlers for any data-time-picker option
    STUDIP.UI.Timepicker.dataHandlers = {
    //     // Ensure this time is not later (<=) than another time by setting
    //     // the maximum allowed time on the other time.
    //     // This will also set this time to the maximum allowed time if it is
    //     // currently later than the allowed maximum time.
    //     '<=': function (selector, offset) {
    //         var this_time = $(this).timepicker('getDate'),
    //             max_time = null,
    //             temp;
    //
    //         if ((offset === undefined) && $(selector).data('offset')) {
    //             temp   = $(selector).data('offset');
    //             offset = parseInt($(temp).val(), 10);
    //         }
    //
    //         // Get max time by either actual times
    //         $(selector).each(function () {
    //             var time = $(this).timepicker('getDate') || $(this).timepicker('option', 'maxTime');
    //             if (time && (!max_time || time < max_time)) {
    //                 max_time = new Date(date);
    //             }
    //         });
    //
    //         // Set max date and adjust current date if neccessary
    //         if (max_date) {
    //             max_date.setTime(max_date.getTime() - (offset || 0) * 24 * 60 * 60 * 1000);
    //
    //             if (this_date && this_date > max_date) {
    //                 $(this).datetimepicker('setDate', max_date);
    //             }
    //
    //             $(this).timepicker('option', 'maxDate', max_date);
    //         } else {
    //             $(this).datetimepicker('option', 'maxDate', null);
    //         }
    //     },
    //     // Ensure this date is earlier (<) than another date by setting the
    //     // maximum allowed date to the other date - 1 day.
    //     // This will also set this date to the maximum allowed date - 1 day
    //     // if it is currently later than the allowed maximum date.
    //     '<': function (selector) {
    //         STUDIP.UI.Timepicker.dataHandlers['<='].call(this, selector, 1);
    //     },
    //     // Ensure this date is not earlier (>=) than another date by setting
    //     // the minimum allowed date to the other date.
    //     // This will also set this date to the minimum allowed date if it is
    //     // currently earlier than the allowed minimum date.
    //     '>=': function (selector, offset) {
    //         var this_date = $(this).datetimepicker('getDate'),
    //             min_date = null,
    //             temp;
    //
    //         if ((offset === undefined) && $(selector).data('offset')) {
    //             temp   = $(selector).data('offset');
    //             offset = parseInt($(temp).val(), 10);
    //         }
    //
    //         // Get min date by either actual dates or minDate options on
    //         // all matching elements
    //         if (selector === 'today') {
    //             min_date = new Date();
    //             min_date.setHours(0, 0, 0);
    //         } else {
    //             $(selector).each(function () {
    //                 var date = $(this).datetimepicker('getDate') || $(this).datetimepicker('option', 'minDate');
    //                 if (date && (!min_date || date > min_date)) {
    //                     min_date = new Date(date);
    //                 }
    //             });
    //         }
    //
    //         // Set min date and adjust current date if neccessary
    //         if (min_date) {
    //             min_date.setTime(min_date.getTime() + (offset || 0) * 24 * 60 * 60 * 1000);
    //
    //             if (this_date && this_date < min_date) {
    //                 $(this).datetimepicker('setDate', min_date);
    //             }
    //
    //             $(this).datetimepicker('option', 'minDate', min_date);
    //         } else {
    //             $(this).datetimepicker('option', 'minDate', null);
    //         }
    //     },
    //     // Ensure this date is later (>) than another date by setting the
    //     // minimum allowed date to the other date + 1 day.
    //     // This will also set this date to the minimum allowed date + 1 day
    //     // if it is currently earlier than the allowed minimum date.
    //     '>': function (selector) {
    //         STUDIP.UI.DateTimepicker.dataHandlers['>='].call(this, selector, 1);
    //     }
    };

    // Apply defaults including date picker handlers
    defaults = $.extend(locale, {
        beforeShow: function (input, inst) {
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
    $.datepicker.setDefaults($.extend(defaults, {
        onClose: function (date, inst) {
            $(this).one('click.picker', function () {
                $(this).datepicker('show');
            }).on('blur', function () {
                $(this).off('click.picker');
            });
        }
    }));
    $.timepicker.setDefaults(defaults);

    // Attach global focus handler on date picker elements
    $(document).on('focus', STUDIP.UI.Datepicker.selector, function () {
        STUDIP.UI.Datepicker.init();
    });

    // Attach global focus handler on datetime picker elements
    $(document).on('focus', STUDIP.UI.DateTimepicker.selector, function () {
        STUDIP.UI.DateTimepicker.init();
    });

    // Attach global focus handler on time picker elements
    $(document).on('focus', STUDIP.UI.Timepicker.selector, function () {
        STUDIP.UI.Timepicker.init();
    });

}(jQuery, STUDIP));
