$(document).on('click', '.smiley-toggle', function(event) {
    var element = $(this);

    element.prop('disabled', true).addClass('ajax');

    $.getJSON(element.attr('href')).then(function(json) {
        var container = $(element)
            .closest('.ui-dialog-content,#layout_content')
            .first();
        $('.messagebox', container).remove();
        container.prepend(json.message);

        element
            .toggleClass('favorite', json.state)
            .removeClass('ajax')
            .prop('disabled', false);
    });
    event.preventDefault();
});
