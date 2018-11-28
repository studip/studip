function findList(selector, context) {
    var list = $(context)
        .closest('.studip-selection')
        .find(selector);
    if (list.is('ul')) {
        return list;
    }
    return list.find('ul:first');
}

$(document).on('click', '.studip-selection:not(.disabled) li:not(.empty-placeholder)', function() {
    var remove = $(this).is('.studip-selection-selected li'),
        item_id = $(this).data().selectionId,
        attr_name =
            $(this)
                .closest('.studip-selection')
                .data().attributeName || 'selected',
        list;
    if (remove) {
        list = findList('.studip-selection-selectable', this);
        $('input[type=hidden]', this).remove();
    } else {
        list = findList('.studip-selection-selected', this);
        $('<input type="hidden" name="' + attr_name + '[]">')
            .val(item_id)
            .prependTo(this);
    }

    $(this)
        .remove()
        .appendTo(list);

    return false;
});
