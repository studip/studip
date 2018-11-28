jQuery(document).ready(function($) {
    $('form.drag-and-drop:not(.files)').each(function() {
        STUDIP.DragAndDropUpload.bind(this);
    });
});
