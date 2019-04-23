/*jslint esversion: 6*/
import "gridstack/dist/gridstack.css"
import "../../stylesheets/widgets.less"

import 'gridstack';
import 'gridstack/dist/gridstack.jQueryUI.js';

import WidgetSystem from '../lib/widget_system.js';

function addDraggable(element, draggable, width) {
    var helper = draggable.clone();
    var recreate = false;
    element.append(helper.width(width));

    helper.draggable({
        appendTo: 'body',
        helper: function() {
            return $(this).clone().css({
                zIndex: 1000
            });
        },
        revert: function(droppable) {
            if (droppable !== false) {
                addDraggable(element, draggable, width);
                $('#layout-sidebar').removeClass('second-display');
                return false;
            }

            recreate = true;
            return true;
        },
        stop: function() {
            // Recreate draggable after revert. This is neccessary since
            // gridstack occassionally fails with "origNode._grid is
            // undefined". This will prevent that.
            if (recreate) {
                helper.draggable('destroy').remove();
                addDraggable(element, draggable, width);
            }
        }
    });
}

function getAddableWidgetList(url) {
    return $.Deferred(function(dfd) {
        if ($('#layout-sidebar .sidebar-secondary-widget').length > 0) {
            return dfd.resolve();
        }

        $.get(url).then(function(response) {
            var content = $(response);
            var container_id = $('.addable-widgets', content).data().containerId;
            var widgetsystem = STUDIP.WidgetSystem.get(container_id);
            var elements = $('.addable-widgets div[data-widget-id]', content);
            var one_width = Math.floor($(widgetsystem.grid).width() / widgetsystem.width);

            $(content).appendTo('#layout-sidebar > .sidebar');

            $(elements).each(function() {
                var widget_id = $(this).data().widgetId;
                var title = $('h2', this).html();
                var contents = $(this).children(':not(h2)').clone();
                var helper = $('<div class="grid-stack-item widget-to-add" data-gs-width="1" data-gs-height="1">');
                var body = $('<div class="grid-stack-item-content has-layout">').appendTo(helper);
                var header = $('<header class="widget-header">').appendTo(body);

                helper.attr('data-widget-id', widget_id);

                $('<h2 class="widget-title">').html(title).appendTo(header);
                $('<article class="widget-content">').append(contents).appendTo(body);

                addDraggable($(this).parent(), helper, one_width);
            });

            // This will position the addable widget under the mouse cursor
            // so that the whole li containing the widget may be used to
            // drag the element to the grid. Otherwise there's a gap to the
            // right.
            $('#layout-sidebar .addable-widgets li').on('mousemove', function(event) {
                var offset = $(this).offset();
                var position = {
                    left: event.pageX - offset.left - 16,
                    top: event.pageY - offset.top - 16
                };

                $('.widget-to-add', this).css(position);
            });

            dfd.resolve();
        }, dfd.reject);
    }).promise();
}

$(document).on('widget-add', (event, jqxhr) => {
    var remove = jqxhr.getResponseHeader('X-Widget-Remove');
    var widget_id = jqxhr.getResponseHeader('X-Widget-Id');
    if (remove) {
        $('.addable-widgets li:has([data-widget-id="' + widget_id + '"])').each(function() {
            $('.ui-draggable', this).draggable('destroy');
            $(this).slideUp(() => $(this).remove());
        });
    }
}).on('widget-remove', (event, jqxhr) => {
    if (jqxhr.getResponseHeader('X-Refresh')) {
        $('#layout-sidebar .sidebar-secondary-widget').remove();
    }
}).on('click', (event) => {
    if ($(event.target).closest('.sidebar-secondary-widget').length === 0) {
        $('#layout-sidebar').removeClass('second-display');
    }
});

$('#layout-sidebar').on('click', '.widget-add-toggle', function() {
    getAddableWidgetList(this.href).done(function() {
        $('#layout-sidebar').toggleClass('second-display');
    });

    return false;
});

STUDIP.WidgetSystem = WidgetSystem;
