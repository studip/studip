/*jslint esversion: 6*/
STUDIP.domReady(() => {
    // Initialize widget system itself and possible instances on page
    $('.grid-stack').each(function () {
        // async load the widgetsystem, then enhance
        STUDIP.loadChunk('widgetsystem').then(() => {
            STUDIP.WidgetSystem.initialize(this);
        });
    });
});
