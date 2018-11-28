/**
 * Handle oversized a.k.a. "big" images that are originally greater in
 * width or height than they are displayed.
 *
 * Any oversized image will be clickable and is displayed in an overlay
 * as long as it does not meet certain criteria that will exclude it from
 * this mechanism (see method shouldSkip for more info).
 *
 * The big image handler my be enabled and disabled by an api bound to
 * the global STUDIP object (see methods STUDIP.BigImageHandler.enable and
 * STUDIP.BigImageHandler.disable).
 *
 * Images are only handled if they exceed a certain threshold in any
 * direction. This threshold can be adjusted in the variable
 * STUDIP.BigImageHandler.threshold.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
var pixelRatio = window.devicePixelRatio || 1,
    dataAttribute = 'big-image-handled';

// Determines whether the image should not be handled due to one of the
// following reasons:
//
// - image is inside an editable element (wysiwyg)
// - image is an avatar
// - image is an icon
// - image is a svg
// - image is linked to something else than itself
// - image has the class "ignore-size"
function shouldSkip(img) {
    var $img = $(img),
        $link = $img.closest('a'),
        src = $img.attr('src');
    return (
        $img.data(dataAttribute) ||
        $img.closest('[contenteditable]').length > 0 ||
        ($link.length > 0 && $link.attr('href') !== src) ||
        src.match(/\.svg$/)
    );
}

// The actual handler for images. This determines whether the image
// is considered big and should be treated that way.
// If the image is oversized, store the actual width and height of the
// image in it's data storage and add the "oversized-image" class to it.
//
// This function will return a function to be used as an onload handler.
function oversizedHandler(img) {
    var display_width = Math.max(BigImageHandler.threshold, parseInt($(img).width(), 10)),
        display_height = Math.max(BigImageHandler.threshold, parseInt($(img).height(), 10));
    return function() {
        var width = this.width,
            height = this.height,
            title =
                $(this).prop('title') ||
                'Dieses Bild wird verkleinert dargestellt. Klicken Sie für eine größere Darstellung.'.toLocaleString(),
            highdpi_check = width / display_width === pixelRatio && height / display_height === pixelRatio;
        if (!highdpi_check && (width > display_width || height > display_height)) {
            $(img)
                .data('oversized', {
                    width: width,
                    height: height
                })
                .prop('title', title)
                .addClass('oversized-image');
        }
    };
}

// Set up global js api
const BigImageHandler = {
    // Threshold for activating the handler, images must be greater
    // than this value in any direction to trigger the handler
    threshold: 64
};

// Enables the mechanism
BigImageHandler.enable = function() {
    // Global handlers:
    // - check if an image is oversized on mouseenter
    // - create overlay/zoom on click on the image
    // - remove overlay/zoom on click on itself or escape key
    $(document)
        .on('mouseenter.big-image-handler', '.formatted-content img', function() {
            if (!shouldSkip(this)) {
                var img = new Image();
                img.onload = oversizedHandler(this);
                img.src = this.src;
                $(this).data(dataAttribute, true);
            }
        })
        .on('click.big-image-handler', 'img.oversized-image', function(event) {
            var src = $(this).attr('src'),
                data = $(this).data('oversized'),
                zoomed = $('<span>').css('background-image', 'url(' + src + ')'),
                overlay = $('<div class="oversized-image-zoom">');

            // Set dimensions
            zoomed.width(data.width);
            zoomed.height(data.height);

            // Add invisible image (see css) to allow right click "view image"
            $('<img>')
                .attr('src', src)
                .appendTo(zoomed);

            // Append overlay
            overlay.append(zoomed).appendTo('body');

            // Stop event
            event.stopPropagation();
            event.preventDefault();
        })
        .on('click.big-image-handler', '.oversized-image-zoom', function() {
            // remove overlay
            $(this).remove();
        })
        .on('keypress.big-image-handler', 'body:has(.oversized-image-zoom)', function(event) {
            if (event.key === 'Escape') {
                $('.oversized-image-zoom').remove();

                event.preventDefault();
                event.stopPropagation();
            }
        });
};

// Disable the mechanism
BigImageHandler.disable = function() {
    $('img.oversized-image').removeClass('oversized-image');
    $(document).off('.big-image-handler');
};

export default BigImageHandler;
