//must be overridden to display html in autocomplete like avatars:
$.widget('studip.quicksearch', $.ui.autocomplete, {
    _renderItem (ul, item) {
        let li = $('<li>');
        li.data('item.autocomplete', item);
        if (item.disabled) {
            li.addClass('ui-state-disabled');
        }
        $('<a>').html(item.label).appendTo(li);
        li.appendTo(ul);

        return li;
    },

    _renderMenu (ul, items) {
        $(ul).addClass('studip-quicksearch');
        items.forEach((item) => {
            this._renderItemData(ul, item);
        });
    }
});
