STUDIP.domReady(() => {
    $.extend($.ui.multiselect, {
        locale: {
            addAll: 'Alle hinzufügen'.toLocaleString(),
            removeAll: 'Alle entfernen'.toLocaleString(),
            itemsCount: 'ausgewählt'.toLocaleString()
        }
    });
});
