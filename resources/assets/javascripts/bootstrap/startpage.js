STUDIP.domReady(() => {
    if ($('html').is(':not(.responsive-display)')) {
        STUDIP.startpage.init();
    }
});

// Add handler for "read all" on news widget
$(document).on('click', '#start-index a[href*="newswidget/read_all"]', function(event) {
    var icon = $(this),
        url = icon.attr('href'),
        widget = icon.closest('.studip-widget');

    icon.prop('disabled', true).addClass('ajaxing');

    $.getJSON(url).then(function(response) {
        if (response) {
            $('article.new', widget).removeClass('new');
            $('.news-comments-unread', widget)
                .removeClass('news-comments-unread')
                .removeAttr('title');

            // It is approriate to use attr() to modify data here since
            // the attribute's value is displayed via css, thus it needs
            // to be actually in the DOM.
            $('#nav_start [data-badge]')
                .attr('data-badge', 0)
                .trigger('badgechange');

            icon.remove();
        }
    });

    event.preventDefault();
});
