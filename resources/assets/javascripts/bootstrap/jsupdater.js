// Start js updater if global settings says so
$(window).on('load', function() {
    if (STUDIP.jsupdate_enable) {
        STUDIP.JSUpdater.start();
    }
});

// Try to stop js updater if window is unloaded (might not work in all
// browsers)
$(window).on('unload', STUDIP.JSUpdater.stop);
