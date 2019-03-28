STUDIP.ready((event) => {
    $('form.drag-and-drop:not(.files)', event.target).each(function() {
        STUDIP.DragAndDropUpload.bind(this);
    });
});
