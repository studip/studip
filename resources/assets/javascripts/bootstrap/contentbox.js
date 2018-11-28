$(document).on('click', 'section.contentbox article header h1 a', function(e) {
    if (!$(this).hasClass('no-contentbox-link')) {
        e.preventDefault();
        var article = $(this).closest('article');

        // If the contentbox article is new send an ajax request
        if (article.hasClass('new')) {
            $.ajax({
                type: 'POST',
                url: STUDIP.URLHelper.getURL(decodeURIComponent(article.data('visiturl') + $(this).attr('href')))
            });
        }

        // Open the contentbox
        article.toggleClass('open').removeClass('new');
    }
});
