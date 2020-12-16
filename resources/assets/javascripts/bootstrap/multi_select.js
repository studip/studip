import { _ } from '../lib/gettext.js';

STUDIP.domReady(() => {
    $.extend($.ui.multiselect, {
        locale: {
            addAll: _('Alle hinzufügen'),
            removeAll: _('Alle entfernen'),
            itemsCount: _('ausgewählt')
        }
    });
});
