// Hide sink on touch elsewhere
$(document).on('touchstart', function (event) {
    if ($(event.target).closest('li.overflow').length === 0) {
        $('#header-sink').prop('checked', false);
    }
    if ($(event.target).closest('li.has-subnavigation').length === 0) {
        $('.responsive-toggle').prop('checked', false);
    }
});

// Reshrink on resize
$(window).on('resize', _.debounce(STUDIP.NavigationShrinker, 100));

// Shrink on domready
STUDIP.domReady(STUDIP.NavigationShrinker);

STUDIP.domReady(() => {
    $('.new_context').removeClass('contextless');
});
