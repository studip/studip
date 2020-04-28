/**
 * Message reporting
 *
 * @author      Viktoria Wiebe
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version     1.0
 * @since       Stud.IP 4.5
 * @license     GLP2 or any later version
 * @copyright   2019 Stud.IP Core Group
 */

import Dialog from './dialog.js';

let counter = 0;

function reportMessage(type, title, content, options) {
    options.id          = `report-${type}-${counter++}`;
    options.title       = title;
    options.size        = 'fit';
    options.wikilink    = false;
    options.dialogClass = `report-${type}`;

    Dialog.show(content, options);
}

const Report = {
    // Info message
    info (title, content, options = {}) {
        reportMessage('info', title, content, options);
    },

    // Success message
    success (title, content, options = {}) {
        reportMessage('success', title, content, options);
    },

    // Warning message
    warning (title, content, options = {}) {
        reportMessage('warning', title, content, options);
    },

    // Error message
    error (title, content, options = {}) {
        reportMessage('error', title, content, options);
    }
};

export default Report;
