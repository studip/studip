$(document).on('click', '.remove_topic', STUDIP.Dates.removeTopicFromIcon);

// Drag and drop support for topics in date list
function createDraggable() {
    $('.dates.has-access tbody tr:not(:only-child) .themen-list li > a.title:not(.draggable-topic)').each(function() {
        var table_id = $(this)
            .closest('table')
            .data().tableId;

        $(this)
            .children()
            .addClass('draggable-topic-handle');

        $(this)
            .closest('li')
            .addClass('draggable-topic')
            .data('table-id', table_id)
            .attr('data-table-id', table_id)
            .draggable({
                axis: 'y',
                containment: $(this).closest('tbody'),
                handle: '.draggable-topic-handle',
                revert: true
            });
    });
}

STUDIP.domReady(function () {
    if ($('body#course-dates-index').length === 0) {
        return;
    }

    $(document).ajaxComplete(createDraggable);

    $('.themen-list').each(function() {
        var table_id = $(this)
            .closest('table')
            .data().tableId;
        $(this)
            .closest('td')
            .addClass('topic-droppable')
            .droppable({
                accept: '.draggable-topic[data-table-id="' + table_id + '"]',
                activeClass: 'active',
                hoverClass: 'hovered',
                drop: function(event, ui) {
                    var context = $(ui.draggable),
                        topic = context.closest('li').data().issue_id,
                        source = context.closest('tr').data().terminId,
                        target = $(this)
                            .closest('tr')
                            .data().terminId,
                        path = ['dispatch.php/course/dates/move_topic', topic, source, target].join('/'),
                        url = STUDIP.URLHelper.getURL(path),
                        cell = $(this);

                    if (source === target) {
                        return;
                    }

                    ui.draggable.draggable('option', 'revert', false);

                    $.post(url).done(function(response) {
                        ui.draggable
                            .draggable('destroy')
                            .closest('li')
                            .remove();
                        $('ul', cell).append(response);
                    });
                }
            });
    });

    createDraggable();
});
