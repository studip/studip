/*jslint browser: true */
/*global jQuery, STUDIP */

STUDIP.QuickSelection = {
    update: function (html) {
        jQuery('#quickSelectionWrap').replaceWith(html);
    }
};
