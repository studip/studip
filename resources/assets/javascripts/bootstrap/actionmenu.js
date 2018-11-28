/*jslint browser: true, indent: 4 */
/*global jQuery */

(function ($) {
    'use strict';

    // Open action menu on click on the icon
    $(document).on('mousedown focus', '.action-menu-icon', function (event) {
        var menu = $(this).closest('.action-menu');
        if ((event.type !== "mousedown") && menu.is(".bymousedown")) {
            menu.removeClass("bymousedown");
            event.stopPropagation();
            return false;
        }
        if (event.type === "mousedown") {
            menu.addClass("bymousedown");
        }

        // Close other menus (and remove contentbox overflow handling)
        if (!menu.is('.active')) {
            $('.action-menu').removeClass('active')
                .parents().removeClass('force-visible-overflow');
        }

        // Open menu (and force visibility on contentbox parent elements)
        $(this).closest('.action-menu').toggleClass('active')
            .filter('.active').parents().filter(function () {
                return $(this).is('p, section, div')
                    && $(this).parent().is('section.contentbox > article');
            }).addClass('force-visible-overflow');

        $(this).attr("aria-expanded", $(this).closest('.action-menu').is(".active") ? "true" : "false");
        // Stop event so the following close event will not be fired
        event.stopPropagation();
    });
    $(document).on('click', '.action-menu-icon', function (event) {
        return false;
    });

    // Close action menu on click outside
    $(document).on('click', function (event) {
        if ($(event.target).closest('.action-menu.active').length === 0) {
            $('.action-menu').removeClass('active')
                .parents().removeClass('force-visible-overflow');
        }
    });

}(jQuery));
