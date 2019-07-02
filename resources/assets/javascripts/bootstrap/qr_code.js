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
})
