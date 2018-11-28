function setCookie(name, value, expiry_days) {
    var chunks = [name + '=' + value],
        date;
    if (expiry_days !== undefined) {
        date = new Date();
        date.setTime(date.getTime() + expiry_days * 24 * 60 * 60 * 1000);

        chunks.push('expires=' + date.toUTCString());
    }
    chunks.push(
        'path=/' +
            STUDIP.URLHelper.getURL('a', true)
                .slice(0, -1)
                .split('/')
                .slice(3)
                .join('/')
    );

    document.cookie = chunks.join(';');
}

// Enable shrinking of navigation
var shrinker = function() {
    var main = $('#barTopMenu'),
        sink = $('li.overflow', main),
        x = 0,
        index = false,
        total = 0;
    if (main.length === 0 || sink.length === 0) {
        return;
    }

    // Reset sink (hide and lose all content)
    main.removeClass('overflown');
    $('> label > a', sink).removeAttr('data-badge');
    $('li', sink)
        .remove()
        .insertBefore(sink);

    if ($('html').is('.responsive-display')) {
        return;
    }

    $('li:not(.overflow)', main).each(function(idx) {
        var this_x = $(this).position().left;
        if (this_x > x) {
            x = this_x;
        } else {
            index = idx;
            return false;
        }
    });

    if (index !== false) {
        $('li:not(.overflow)', main)
            .slice(index - 2)
            .detach()
            .prependTo($('ul', sink))
            .each(function() {
                total += parseInt($('a', this).data().badge, 10) || 0;
            });

        main.addClass('overflown');
        $('> label > a', sink).attr('data-badge', total);
    }

    setCookie('navigation-length', main.children(':not(.overflow)').length, 30);
};

export default shrinker;
