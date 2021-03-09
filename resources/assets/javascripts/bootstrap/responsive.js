/*jslint esversion: 6*/

// Build responsive menu on domready or resize
STUDIP.domReady(() => {
    const cache = STUDIP.Cache.getInstance('responsive.');
    if (STUDIP.Navigation.navigation !== undefined) {
        cache.set('navigation', STUDIP.Navigation.navigation);
        STUDIP.Cookie.set('responsive-navigation-hash', STUDIP.Navigation.hash);
    } else {
        STUDIP.Navigation.navigation = cache.get('navigation');
    }

    STUDIP.Responsive.engage();
}, true);

// Trigger search in responsive display
$(document).on('click', '#quicksearch .quicksearchbutton', function() {
    if ($('html').is(':not(.responsive-display)') || $('#quicksearch').is('.open')) {
        return;
    }

    $('#quicksearch').addClass('open');
    $('.quicksearchbox').focus();

    return false;
}).on('blur', '#quicksearch.open .quicksearchbox', function() {
    if (!this.value.trim().length) {
        $('#quicksearch').removeClass('open');
    }
}).on('autocompleteopen', event => {
    if ($(event.target).closest('#quicksearch').length === 0) {
        return;
    }
    $('body > .ui-autocomplete').css({
        left: 0,
        right: 0,
        boxSizing: 'border-box'
    });
});
