const startpage = {
    init: function() {
        $('.start-widgetcontainer .portal-widget-list').sortable({
            handle: '.widget-header',
            connectWith: 'ul.portal-widget-list',
            start: function() {
                $(this)
                    .closest('.start-widgetcontainer')
                    .find('.portal-widget-list')
                    .addClass('ui-sortable move');
            },
            stop: function(event, ui) {
                $.get(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/start/storeNewOrder', {
                    widget: $(ui.item).attr('id'),
                    position: $(ui.item).index(),
                    column: $(ui.item)
                        .parent()
                        .index()
                });
                $(this)
                    .closest('.start-widgetcontainer')
                    .find('.portal-widget-list')
                    .removeClass('move');
            }
        });
    },

    init_edit: function(perm) {
        $('.edit-widgetcontainer .portal-widget-list').sortable({
            handle: '.widget-header',
            connectWith: '.edit-widgetcontainer .portal-widget-list',
            start: function() {
                $(this)
                    .closest('.edit-widgetcontainer')
                    .find('.portal-widget-list')
                    .addClass('ui-sortable move');
            },
            stop: function() {
                // store the whole widget constellation
                var widgets = {
                    left: {},
                    right: {}
                };

                $('.edit-widgetcontainer .start-widgetcontainer .portal-widget-list:first-child > li').each(function() {
                    widgets.left[$(this).attr('id')] = $(this).index();
                });

                $('.edit-widgetcontainer .start-widgetcontainer .portal-widget-list:last-child > li').each(function() {
                    widgets.right[$(this).attr('id')] = $(this).index();
                });

                $.post(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/start/update_defaults/' + perm, widgets);

                $(this)
                    .closest('.edit-widgetcontainer')
                    .find('.portal-widget-list')
                    .removeClass('move');
            }
        });
    }
};

export default startpage;
