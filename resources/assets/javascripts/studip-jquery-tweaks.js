/*jslint browser: true */
/*global jQuery */

/**
 * SVG class handling.
 *
 * This tweaks jQuery so that calls of addClass(), removeClass() and hasClass()
 * don't fail on svg elements.
 *
 * SVGs don't have a className attribute but rather a classList object
 * so the native jQuery methods will have no effect on SVG elements.
 */
(function ($) {
    'use strict';

    var originals = {
        addClass: $.fn.addClass,
        removeClass: $.fn.removeClass,
        hasClass: $.fn.hasClass
    };

    $.fn.addClass = function (value) {
        if (jQuery.isFunction(value)) {
            return originals.addClass.call(this, value);
        }

        this.filter('svg').each(function () {
            var classes = (value || '').trim().split(/\s+/) || [];

            this.classList.add.apply(this.classList, classes);
        });
        originals.addClass.call(this.not('svg'), value);

        return this;
    };

    $.fn.removeClass = function (value) {
        if (jQuery.isFunction(value)) {
            return originals.removeClass.call(this, value);
        }

        this.filter('svg').each(function () {
            var classes = (value || '').trim().split(/\s+/) || [];

            this.classList.remove.apply(this.classList, classes);
        });
        originals.removeClass.call(this.not('svg'), value);

        return this;
    };

    $.fn.hasClass = function (value) {
        var svgs = $(this).filter('svg'),
            i,
            l = svgs.length;
        if (l > 0) {
            for (i = 0; i < l; i += 1) {
                if (svgs.get(i).classList.contains(value)) {
                    return true;
                }
            }
            return false;
        }
        return originals.hasClass.call(this, value);
    };

}(jQuery));