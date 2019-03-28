STUDIP.domReady(() => {
    $('a.get-course-members').on('click', function() {
        var dataEl = $('article#course-members-' + $(this).data('course-id')),
            url;
        if ($.trim(dataEl.html()).length === 0) {
            url = $(this).data('get-members-url');

            dataEl.html(
                $('<img>').attr({
                    width: 32,
                    height: 32,
                    src: STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg'
                })
            );

            $.get(url).done(function(html) {
                dataEl.html(html);
            });
        }
    });
});
