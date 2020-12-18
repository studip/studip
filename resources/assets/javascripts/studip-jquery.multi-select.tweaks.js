import { $gettext } from './lib/gettext.js';

/*jslint browser: true */
/*global jQuery */

/**
 * Neccessary tweaks/adjustments for the jQuery UI multiselect addon.
 *
 * The tweaks essentially enable the list elements to include images as well
 * as newlines and to create disabled elements without the addon interfering.
 *
 * This is accomplished by refining the defined methods of the addon:
 *
 * - For icons, the generateLisFromOption() checks for a special format of
 *   the text parameter and injects a background image to the generated
 *   option element
 * - New lines are created by adjusting the escapeHTML method, any new line
 *   character is replaced by <br>
 * - The disabled elements require a hack involving the methods
 *   generateLisFromOption, addOption and the jQuery extension insertAt.
 *   If the item should be disabled, the index is set to -1 and handled in
 *   each and every method as it would not have been set in the first place.
 *   This way, a disabled item is always added at the end of the list but
 *   this is exactly what we want to achieve.
 *
 * Note:
 *
 * With every update of the multi select addon, this needs to be checked and
 * eventually adjusted to the new conditions.
 */
(function ($, MultiSelect) {
    'use strict';

    var originals = {
        generateLisFromOption: MultiSelect.prototype.generateLisFromOption,
        addOption: MultiSelect.prototype.addOption,
        escapeHTML: MultiSelect.prototype.escapeHTML,
        insertAt: $.fn.insertAt
    };

    MultiSelect.prototype.generateLisFromOption = function (option, index, $container) {
        var $option       = $(option),
            chunks        = $option.text().split('--');

        if (index === -1) {
            $option.prop('disabled', true);
            index = undefined;
        }

        if (chunks.length > 1) {
            $option.attr('style', 'background-image: url(' + chunks.shift()  + ')');

            $option.text(chunks.join("\n"));

            if ($option.is(':disabled')) {
                $option.attr('title', $gettext('Die Person ist bereits eingetragen.'));
            }
        }

        originals.generateLisFromOption.call(this, $option.get(0), index, $container);
    };

    MultiSelect.prototype.addOption = function (options) {
        if (options.disabled) {
            options.index = -1;
            delete options.disabled;
        }
        options.text = this.escapeHTML(options.text);
        return originals.addOption.call(this, options);
    };

    MultiSelect.prototype.escapeHTML = function (text) {
        var result = originals.escapeHTML.call(this, text);
        return result.replace("\n", '<br>');
    };

    $.fn.insertAt = function (index, $parent) {
        if (index === -1) {
            index = $parent.children().length;
        }

        return originals.insertAt.call(this, index, $parent);
    };


}(jQuery, jQuery.fn.multiSelect.Constructor));