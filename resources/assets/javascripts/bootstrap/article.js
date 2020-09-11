/*jslint browser: true */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    $(document).on('click', 'article.studip.toggle header h1 a', function (e) {
        e.preventDefault();

        var article = $(this).closest('article');

        // If the contentbox article is new send an ajax request
        if (article.hasClass('new') && article.data('visiturl')) {
            $.post(STUDIP.URLHelper.getURL(decodeURIComponent(article.data('visiturl') + $(this).attr('href'))));
        }

        // Open the contentbox
        article.toggleClass('open').removeClass('new');
    });

    // Open closed article contents when location hash matches
    $(window).on('hashchange', (event) => {
        const hash = location.hash.split('#').pop();
        $(`article.studip.toggle:not(.open) header h1 a[name="${hash}"]`).click();
    });

    STUDIP.ready(() => {
        const hash = location.hash.split('#').pop();
        if (hash.length > 0) {
            $(`article.studip.toggle:not(.open) header h1 a[name="${hash}"]`).click();
        }
    });
}(jQuery, STUDIP));
