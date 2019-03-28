$(document).on('click', '.tabbable-widget > nav > a', function(event) {
    var selector = $(this).attr('href');
    $(this)
        .addClass('active')
        .siblings()
        .removeClass('active');
    $(selector)
        .addClass('active')
        .siblings('section')
        .removeClass('active')
        .end();

    // Delay resetting of scroll top until browser is no longer busy
    // (otherwise the scrolled element will not reset - at least in FF)
    setTimeout(function() {
        $(selector).scrollTop(0);
    }, 0);

    if (history.pushState) {
        history.pushState(null, null, selector);
    }

    event.preventDefault();
});
STUDIP.domReady(() => {
    if (!location.hash) {
        return;
    }

    $('.tabbable-widget > nav > a[href="' + location.hash + '"]').click();
});
