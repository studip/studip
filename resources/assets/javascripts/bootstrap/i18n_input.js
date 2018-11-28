$(document).ready(function() {
    STUDIP.i18n.init();
});

$(document).on('dialog-update', function(event, data) {
    STUDIP.i18n.init(data.dialog);
});
