jQuery(
    function () {

        jQuery(document).ready(
            function() {
                var elements = jQuery('[data-inline-editing]');
                for (element of elements) {
                    STUDIP.InlineEditing.init(element);
                }
            }
        );

        jQuery(document).on(
            'dialog-update',
            null,
            function() {
                var elements = jQuery('.ui-dialog [data-inline-editing]');
                for (element of elements) {
                    STUDIP.InlineEditing.init(element);
                }
            }
        );

        jQuery(document).on(
            'click',
            '[data-inline-editing] .edit-button',
            function (event) {
                STUDIP.InlineEditing.activate(event.target);
            }
        );

        jQuery(document).on(
            'click',
            '[data-inline-editing] .save-button',
            function (event) {
                STUDIP.InlineEditing.save(event.target);
            }
        );

        jQuery(document).on(
            'click',
            '[data-inline-editing] .abort-button',
            function (event) {
                STUDIP.InlineEditing.abort(event.target);
            }
        );
    }
);
