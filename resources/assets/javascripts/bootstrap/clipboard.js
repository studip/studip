STUDIP.domReady(function() {
    jQuery('.clipboard-draggable-item').draggable(
        {
            cursorAt: {left: 28, top: 15},
            appendTo: 'body',
            helper: function () {
                var dragged_item = jQuery('<div class="dragged-clipboard-item"></div>');
                jQuery(dragged_item).data('id', jQuery(this).data('id'));
                jQuery(dragged_item).data('range_type', jQuery(this).data('range_type'));
                jQuery(dragged_item).text(jQuery(this).data('name'));
                return dragged_item;
            },
            revert: true,
            revertDuration: 0
        }
    );

    jQuery('.clipboard-area').droppable(
        {
            drop: STUDIP.Clipboard.handleItemDrop
        }
    );

    jQuery('.clipboard-selector').change(
        STUDIP.Clipboard.switchClipboard
    );

    jQuery(document).on(
        'change',
        '.clipboard-selector',
        STUDIP.Clipboard.switchClipboard
    );

    jQuery(document).on(
        'dragend',
        '.clipboard-draggable-item',
        function(event) {
            jQuery(event.target).css(
                {
                    'top': '0px',
                    'left': '0px'
                }
            );
        }
    );

    jQuery(document).on(
        'dragover',
        '.clipboard-area',
        function(event) {
            event.preventDefault();
            event.stopPropagation();
        }
    );

    jQuery(document).on(
        'dragenter',
        '.clipboard-area',
        function(event) {
            //TODO:rrv2: use CSS classes!
            event.target.style.backgroundColor = '#0F0';
        }
    );

    jQuery(document).on(
        'dragleave',
        '.clipboard-area',
        function(event) {
            //TODO:rrv2: use CSS classes!
            event.target.style.backgroundColor = '#FFF';
        }
    );

    jQuery(document).on(
        'click',
        '.clipboard-remove-button',
        STUDIP.Clipboard.confirmRemoveClick
    );

    jQuery(document).on(
        'click',
        '.clipboard-item-remove-button',
        STUDIP.Clipboard.confirmRemoveItemClick
    );

    jQuery('.clipboard-widget .new-clipboard-form').submit(
        STUDIP.Clipboard.handleAddForm
    );

    jQuery(document).on(
        'click',
        '.clipboard-add-item-button',
        STUDIP.Clipboard.handleAddItemButtonClick
    );
});
