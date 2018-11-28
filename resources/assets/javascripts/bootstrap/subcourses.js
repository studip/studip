// Open action menu on click on the icon
$(document).on('click', '.toggle-subcourses', function(event) {
    var row = $(this).closest('tr');

    if ($(this).hasClass('open')) {
        $(this).removeClass('open');
        $(this)
            .children('.icon-shape-remove')
            .addClass('hidden-js');
        $(this)
            .children('.icon-shape-add')
            .removeClass('hidden-js');
        $(
            'tr.subcourse-' +
                $(this)
                    .closest('tr')
                    .data('course-id')
        ).addClass('hidden-js');
        row.removeClass('has-subcourses');
    } else if ($(this).hasClass('loaded')) {
        $(this).addClass('open');
        $(this)
            .children('.icon-shape-add')
            .addClass('hidden-js');
        $(this)
            .children('.icon-shape-remove')
            .removeClass('hidden-js');
        $(
            'tr.subcourse-' +
                $(this)
                    .closest('tr')
                    .data('course-id')
        ).removeClass('hidden-js');
        row.addClass('has-subcourses');
    } else {
        $.ajax($(this).data('get-subcourses-url'), {
            beforeSend: function(xhr, settings) {
                $('<div class="loading" style="padding: 10px">')
                    .html(
                        $('<img>')
                            .attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg')
                            .css('width', '24')
                            .css('height', '24')
                    )
                    .insertAfter(row);
            },
            success: function(data, status, xhr) {
                $(row)
                    .siblings('div.loading')
                    .remove();
                $(data).insertAfter(row);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Status: ' + textStatus + '\nError: ' + errorThrown);
            }
        });
        $(this)
            .addClass('loaded')
            .addClass('open');
        $(this)
            .children('.icon-shape-add')
            .addClass('hidden-js');
        $(this)
            .children('.icon-shape-remove')
            .removeClass('hidden-js');
        row.addClass('has-subcourses');
    }

    // Stop event so the following close event will not be fired
    event.stopPropagation();

    return false;
});
