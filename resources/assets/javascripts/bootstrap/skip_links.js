STUDIP.domReady(STUDIP.SkipLinks.initialize);

jQuery(document).on('keyup', STUDIP.SkipLinks.showSkipLinkNavigation);
jQuery(document).on('click', function(event) {
    if (!jQuery(event.target).is('#skip_link_navigation a')) {
        STUDIP.SkipLinks.moveSkipLinkNavigationOut();
    }
});
