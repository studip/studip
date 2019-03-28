/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */
/* ------------------------------------------------------------------------
 * application.js
 * This file is part of Stud.IP - http://www.studip.de
 *
 * Stud.IP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Stud.IP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Stud.IP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

 /* ------------------------------------------------------------------------
  * add classes to html element according to horizontal screen size
  * ------------------------------------------------------------------------ */
(function ($) {
    // These sizes must match the breakpoints defined in breakspoints.less
    // TODO: use same webpack configuration for both
    const sizes = {
        tiny: '0px',
        small: '576px',
        medium: '768px',
        large: '1200px'
    };

    for (let size in sizes) {
        if (window.matchMedia('(min-width: ' + sizes[size] + ')').matches) {
            $('html').addClass('size-' + size);
        }
    }
}(jQuery));

/* ------------------------------------------------------------------------
 * messages boxes
 * ------------------------------------------------------------------------ */
jQuery(document).on('click', '.messagebox .messagebox_buttons a', function () {
    if (jQuery(this).is('.details')) {
        jQuery(this).closest('.messagebox').toggleClass('details_hidden');
    } else if (jQuery(this).is('.close')) {
        jQuery(this).closest('.messagebox').hide('blind', 'fast', function () {
            jQuery(this).remove();
        });
    }
    return false;
}).on('focus', '.messagebox .messagebox_buttons a', function () {
    jQuery(this).blur(); // Get rid of the ugly "clicked border" due to the text-indent
});


/* ------------------------------------------------------------------------
 * application wide setup
 * ------------------------------------------------------------------------ */
jQuery(function () {
    // AJAX Indicator
    STUDIP.ajax_indicator = true;

    STUDIP.study_area_selection.initialize();

    // autofocus for all browsers
    if (!("autofocus" in document.createElement("input"))) {
        jQuery('[autofocus]').first().focus();
    }

    if (document.createElement('textarea').style.resize === undefined) {
        jQuery('textarea.resizable').resizable({
            handles: 's',
            minHeight: 50,
            zIndex: 1
        });
    }
});

jQuery(document).on('studip-ready', function (event, type, data) {
    jQuery('.add_toolbar').addToolbar();

    STUDIP.Forms.initialize(data.dialog);
    STUDIP.Markup.element(data.dialog || document);
});


/* ------------------------------------------------------------------------
 * application collapsable tablerows
 * ------------------------------------------------------------------------ */
jQuery(function ($) {

    $(document).on('focus', 'table.collapsable .toggler', function () {
        $(this).blur();
    }).on('click', 'table.collapsable .toggler', function () {
        $(this).closest('tbody').toggleClass('collapsed')
               .filter('.collapsed').find('.action-menu').removeClass('active');
        return false;
    });

    $(document).on('click', 'a.load-in-new-row', function () {
        if ($(this).data('busy')) {
            return false;
        }

        if ($(this).closest('tr').next().hasClass('loaded-details')) {
            $(this).closest('tr').next().remove();
            return false;
        }
        $(this).showAjaxNotification().data('busy', true);

        var that = this;
        $.get($(this).attr('href'), function (response) {
            var row = $('<tr />').addClass('loaded-details');
            $('<td />')
                .attr('colspan', $(that).closest('td').siblings().length + 1)
                .html(response)
                .appendTo(row);

            $(that)
                .hideAjaxNotification()
                .closest('tr').after(row);

            $(that).data('busy', false);
            $('body').trigger('ajaxLoaded');
        });

        return false;
    });

    $(document).on('click', '.loaded-details a.cancel', function () {
        $(this).closest('.loaded-details').prev().find('a.load-in-new-row').click();
        return false;
    });

    $(document).ready(function () {
        var elements = $('.load-in-new-row-open');
        elements.click();
        if (elements.length > 0) {
            $(window).scrollTo(elements.first());
        }
    });

});

/* ------------------------------------------------------------------------
 * Toggle dates in seminar_main
 * ------------------------------------------------------------------------ */
(function ($) {
    $(document).on('click', '.more-dates', function () {
        $('.more-dates-infos').toggle();
        $('.more-dates-digits').toggle();
        if ($('.more-dates-infos').is(':visible')) {
            $('.more-dates').text('(weniger)');
            $('.more-dates').attr('title', 'Blenden Sie die restlichen Termine aus'.toLocaleString());
        } else {
            $('.more-dates').text('(mehr)');
            $('.more-dates').attr('title', 'Blenden Sie die restlichen Termine ein'.toLocaleString());
        }
    });

    $(document).on('click', '.more-location-dates', function () {
        $(this).closest('div').prev().toggle();
        $(this).prev().toggle();

        if ($(this).closest('div').prev().is(':visible')) {
            $(this).text('(weniger)');
            $(this).attr('title', 'Blenden Sie die restlichen Termine aus'.toLocaleString());
        } else {
            $(this).text('(mehr)');
            $(this).attr('title', 'Blenden Sie die restlichen Termine ein'.toLocaleString());
        }
    });
}(jQuery));

/* ------------------------------------------------------------------------
 * only numbers in the input field
 * ------------------------------------------------------------------------ */
jQuery(document).on('keyup', 'input.allow-only-numbers', function () {
    jQuery(this).val(jQuery(this).val().replace(/\D/, ''));
});


/* ------------------------------------------------------------------------
 * additional jQuery (UI) settings for Stud.IP
 * ------------------------------------------------------------------------ */
jQuery.ui.accordion.prototype.options.icons = {
    header: 'arrow_right',
    activeHeader: 'arrow_down'
};
jQuery.extend(jQuery.ui.dialog.prototype.options, {
    closeText: 'Schliessen'.toLocaleString()
});


/* ------------------------------------------------------------------------
 * jQuery timepicker
 * ------------------------------------------------------------------------ */

/* German translation for the jQuery Timepicker Addon */
/* Written by Marvin */
(function ($) {
    $.timepicker.regional.de = {
        timeOnlyTitle: 'Zeit wählen',
        timeText: 'Zeit',
        hourText: 'Stunde',
        minuteText: 'Minute',
        secondText: 'Sekunde',
        millisecText: 'Millisekunde',
        microsecText: 'Mikrosekunde',
        timezoneText: 'Zeitzone',
        currentText: 'Jetzt',
        closeText: 'Fertig',
        timeFormat: "HH:mm",
        amNames: ['vorm.', 'AM', 'A'],
        pmNames: ['nachm.', 'PM', 'P'],
        isRTL: false,
        showTimezone: false
    };
    $.timepicker.setDefaults($.timepicker.regional.de);

    $(document).on('focus', '.has-time-picker', function () {
        $(this).removeClass('has-time-picker').timepicker();
    });
    $(document).on('focus', '.has-time-picker-select', function () {
        $(this).removeClass('has-time-picker-select').timepicker({controlType: 'select'});
    });
}(jQuery));


(function ($) {
    $(document).on('focusout', '.studip-timepicker', function () {
        var time = $(this).val();
        if (time.length > 0 && time.length <= 2) {
            $(this).val(time + ":00");
        } else if (time.indexOf(':') === -1 && time.length > 2) {
            var parts = time.split('');
            parts.splice(-2, 0, ':');
            time = parts.join('');
            $(this).val(time);
        }
    });
}(jQuery))


jQuery(function ($) {
    $(document).on('click', 'a.print_action', function (event) {
        var url_to_print = this.href;
        $('<iframe/>', {
            name: url_to_print,
            src: url_to_print,
            width: '1px',
            height: '1px',
            frameborder: 0
        })
            .css({top: '-99px', position: 'absolute'})
            .appendTo('body')
            .on('load', (function () {
                this.contentWindow.focus();
                this.contentWindow.print();
            }));
        return false;
    });
});

/* Copies a value from a select to another element*/
jQuery(document).on('change', 'select[data-copy-to]', function () {
    var target = jQuery(this).data().copyTo,
        value = jQuery(this).val() || jQuery(target).prop('defaultValue');
    jQuery(target).val(value);
});

jQuery(document).ready(function ($) {
    $('#checkAll').prop('checked', $('.sem_checkbox:checked').length !== 0);
});

// Fix horizontal scroll issue on domready, window load and window resize.
// This also makes the header and footer sticky regarding horizontal scrolling.
jQuery(document).ready(function () {
    var page_margin = ($('#layout_page').outerWidth(true) - $('#layout_page').width()) / 2,
        content_margin = $('#layout_content').outerWidth(true) - $('#layout_content').innerWidth(),
        sidebar_width = $('#layout-sidebar').outerWidth(true);

    function fixScrolling() {
        $('#layout_page').removeClass('oversized').css({
            minWidth: '',
            marginRight: '',
            paddingRight: ''
        });

        var max_width = 0,
            fix_required = $('html').is(':not(.responsified)') && $('#layout_content').get(0).scrollWidth > $('#layout_content').width();

        if (fix_required) {
            $('#layout_content').children().each(function () {
                var width = $(this).get(0).scrollWidth + ($(this).outerWidth(true) - $(this).innerWidth());
                if (width > max_width) {
                    max_width = width;
                }
            });

            $('#layout_page').addClass('oversized').css({
                minWidth: sidebar_width + content_margin + max_width + page_margin,
                marginRight: 0,
                paddingRight: page_margin
            });

            STUDIP.Scroll.addHandler('horizontal-scroll', (function () {
                var last_left = null;
                return function (top, left) {
                    if (last_left !== left) {
                        $('#flex-header,#tabs,#layout_footer,#barBottomContainer').css({
                            transform: 'translate3d(' + left + 'px,0,0)'
                        });
                    }
                    last_left = left;
                };
            }()));
        } else {
            STUDIP.Scroll.removeHandler('horizontal-scroll');
        }
    };

    if ($('.no-touch #layout_content').length > 0) {
        window.matchMedia('screen').addListener(function() {
            // Try to fix now
            fixScrolling();

            // and fix again on window load and resize
            $(window).on('resize load', _.debounce(fixScrolling, 100));
        });
    }
});

jQuery(document).on('click', '.course-admin td .course-completion', function () {
    var href    = $(this).attr('href'),
        timeout = window.setTimeout(function () {
            $(this).addClass('ajaxing');
        }.bind(this), 300);;

    $.getJSON(href).done(function (completion) {
        clearTimeout(timeout);

        $(this).removeClass('ajaxing').attr('data-course-completion', completion);
    }.bind(this));

    return false;
});

// Global handler:
// Toggle a table element. The url of the link will be called, an ajax
// indicator will be shown instead of the element and the whole table row
// will be replaced with the row with the same id from the response.
// Thus, in your controller you only have to execute the appropriate
// action and redraw the page with the new state.
jQuery(document).on('click', 'a[data-behaviour~="ajax-toggle"]', function (event) {
    var $that = jQuery(this),
        href  = $that.attr('href'),
        id    = $that.closest('tr').attr('id');

    $that.prop('disabled', true).addClass('ajaxing');
    jQuery.get(href).done(function (response) {
        var row = jQuery('#' + id, response);
        $that.closest('tr').replaceWith(row);
    });

    event.preventDefault();
});

/* Change open-variable on course-basicdata*/
(function ($) {
    $(document).on('click', 'form[name=course-details] fieldset legend', function () {
        $('#open_variable').attr('value', $(this).parent('fieldset').data('open'));
    });
}(jQuery))
