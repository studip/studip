STUDIP.domReady(() => {
    // Test if the header is actually present
    if ($('#scroll-to-top').length > 0) {
        STUDIP.ScrollToTop.enable();
        STUDIP.ScrollToTop.moveBack();
    }
});
