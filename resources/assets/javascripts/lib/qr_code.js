import QRCodeGenerator from "../vendor/qrcode-04f46c6.js"

const QRCode = {
    show: function() {
        jQuery('#qr_code').remove();
        jQuery("<div id='qr_code'/>").appendTo('body');
        var title = jQuery(this).data('qr-title');
        if (title) {
            jQuery('#qr_code').append('<h1 class="title">' + title + '</h1>');
        } else {
            jQuery('#qr_code').append("<div class='header'/>");
        }
        jQuery('#qr_code').append("<div class='code'/>");
        jQuery('#qr_code').append("<div class='url'/>");
        jQuery('#qr_code').append("<div class='description'/>");

        var code = new QRCodeGenerator(jQuery('#qr_code .code')[0], {
            text: jQuery(this).attr('href'),
            width: 1280,
            height: 1280,
            correctLevel: 3
        });

        jQuery('#qr_code .url').text(jQuery(this).attr('href'));
        jQuery('#qr_code .description').text(jQuery(this).data('qr-code'));
        var print_button_enabled = jQuery(this).data('qr-code-print');
        if (print_button_enabled) {
            var icon_path = STUDIP.URLHelper.getURL(
                'assets/images/icons/blue/print.svg'
            );
            var print_element = jQuery('<img></img>');
            jQuery(print_element).attr('src', icon_path);
            jQuery(print_element).addClass('PrintAction');
            jQuery('#qr_code').append(print_element);
        }

        //jQuery("#qr_code .code").html(jQuery(this).find(".qrcode_image").clone());
        var qr = jQuery('#qr_code')[0];
        if (qr.requestFullscreen) {
            qr.requestFullscreen();
        } else if (qr.msRequestFullscreen) {
            qr.msRequestFullscreen();
        } else if (qr.mozRequestFullScreen) {
            qr.mozRequestFullScreen();
        } else if (qr.webkitRequestFullscreen) {
            qr.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
        return false;
    },
    generate: function (element, text, options = {}) {
        options.text = text;
        if (!options.hasOwnProperty('correctLevel')) {
            options.correctLevel = 3;
        }

        var qrcode = new QRCodeGenerator(element, options);
    }
};

export default QRCode;
