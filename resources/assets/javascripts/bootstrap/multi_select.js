import { $gettext } from '../lib/gettext.js';

STUDIP.domReady(() => {
    $.extend($.ui.multiselect, {
        locale: {
            addAll: $gettext('Alle hinzufügen'),
            removeAll: $gettext('Alle entfernen'),
            itemsCount: $gettext('ausgewählt')
        }
    });
});
