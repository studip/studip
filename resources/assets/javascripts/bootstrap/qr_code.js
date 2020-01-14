jQuery(document).on('click', 'a[data-qr-code]', STUDIP.QRCode.show);

STUDIP.ready((event) => {
    $('code.qr', event.target).each(function () {
        let content = $(this).text().trim();
        let code    = $('<div class="qrcode">').hide();
        STUDIP.QRCode.generate(code[0], content, {
            width: 1024,
            height: 1024
        });
        $(this).replaceWith(code);
        setTimeout(() => code.show(), 0);
    });
    jQuery(document).on(
        'click',
        '#qr_code .PrintAction',
        function() {
            //We must hide the other page elements for the print view functionality.
            //Furthermore we must set the width and height of the qr-code.
            jQuery('#layout_wrapper').css(
                {
                    display: 'none'
                }
            );
            jQuery('#qr_code').addClass('print-view');

            //Now we can print:
            window.print();
        }
    );

    jQuery(document).on(
        'fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange',
        function(event) {
            //After the print action is called
            //we must reset the style changes made above:
            jQuery('#layout_wrapper').removeAttr('style');
        }
    );
});

