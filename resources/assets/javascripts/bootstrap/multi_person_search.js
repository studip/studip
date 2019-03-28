STUDIP.domReady(() => {
    STUDIP.MultiPersonSearch.init();

    // init form if it is loaded without ajax
    if ($('.mpscontainer').length) {
        STUDIP.MultiPersonSearch.dialog($('.mpscontainer').data('dialogname'));
    }
});
