/*global jQuery, STUDIP */
STUDIP.Avatar = {
    cropper: null,

    init: function(inputSelector) {
        jQuery(document).on('change', inputSelector, function() {
            STUDIP.Avatar.readFile(this);

            jQuery(document)
                .off('submit.avatar', 'form.settings-avatar')
                .on('submit.avatar', 'form.settings-avatar', function() {
                    var data = STUDIP.Avatar.cropper.getData();
                    return STUDIP.Avatar.checkImageSize(data);
                });
        });
    },

    readFile: function(input) {
        if (window.FileReader && input.files && input.files[0]) {
            var reader = new window.FileReader();

            if (input.files[0].size <= jQuery(input).data('max-size')) {
                var container = jQuery('div#avatar-preview'),
                    dialog = container.closest('div[role="dialog"]');
                // We are in a modal dialog
                if (dialog.length > 0) {
                    // Adjust maximal cropper container height to dialog dimensions.
                    container.css('height', dialog.height() - 200);
                    container.css('width', dialog.width() - 220);
                    container.css('max-height', dialog.height() - 200);
                    container.css('max-width', dialog.width() - 220);
                    // No dialog, full page.
                } else {
                    dialog = jQuery('#layout_content');
                    // Responsive view.
                    if (jQuery('html').hasClass('responsified')) {
                        // Adjust maximal cropper container height to page dimensions.
                        container.css('height', dialog.height() - 220);
                        container.css('width', 0.95 * dialog.width());
                        container.css('max-height', dialog.height() * 220);
                        container.css('max-width', 0.95 * dialog.width());
                        // Non-dialog, non-responsive view.
                    } else {
                        // Adjust maximal cropper container height to page dimensions.
                        container.css('height', dialog.height() - 100);
                        container.css('width', dialog.width() - 200);
                        container.css('max-height', dialog.height() * 220);
                        container.css('max-width', dialog.width() - 100);
                    }
                }

                reader.onload = function(event) {
                    var image = document.getElementById('new-avatar');
                    if (image) {
                        image.src = event.target.result;

                        import(/* webpackChunkName: "avatarcropper" */ 'cropperjs/dist/cropper.js')
                            .then(function (cropperjs) {
                                var Cropper = cropperjs['default']
                                STUDIP.Avatar.cropper = new Cropper(image, {
                                    aspectRatio: 1,
                                    viewMode: 2
                                });
                            })
                            .catch(function (error) {
                                console.log('An error occurred while loading the croppers lib', error);
                            });
                    }
                };

                reader.readAsDataURL(input.files[0]);

                jQuery('#avatar-buttons').removeClass('hidden-js');
                jQuery('label.file-upload').hide();
                jQuery('#avatar-zoom-in').on('click', function() {
                    STUDIP.Avatar.cropper.zoom(0.1);
                    return false;
                });
                jQuery('#avatar-zoom-out').on('click', function() {
                    STUDIP.Avatar.cropper.zoom(-0.1);
                    return false;
                });
                jQuery('#avatar-rotate-clockwise').on('click', function() {
                    STUDIP.Avatar.cropper.rotate(90);
                    return false;
                });
                jQuery('#avatar-rotate-counter-clockwise').on('click', function() {
                    STUDIP.Avatar.cropper.rotate(-90);
                    return false;
                });

                jQuery('#submit-avatar').on('click', function() {
                    jQuery('#cropped-image').attr('value', STUDIP.Avatar.cropper.getCroppedCanvas().toDataURL('image/jpeg', 0.7));
                });
            } else {
                alert(jQuery(input).data('message-too-large'));
            }
        } else {
            alert("Sorry - your browser doesn't support the FileReader API");
        }
    },

    checkImageSize: function(data) {
        // Show a warning if cropped area is smaller than 250x250px.
        if (data.width < 250 || data.height < 250) {
            return confirm(jQuery('#new-avatar').data('message-too-small'));
        }
        return true;
    }
};

jQuery(function() {
    STUDIP.Avatar.init('#avatar-upload');
});
