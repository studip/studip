/*jslint esversion: 6*/
$(document).on('click', 'a.copyable-link', function (event) {
    event.preventDefault();

    // Create dummy element and position it off screen
    // This element must be "visible" (as in "not hidden") or otherwise
    // the copy command will fail
    var dummy = $('<textarea>').val(this.href).css({
        position: 'absolute',
        left: '-9999px'
    }).appendTo('body');

    // Select text and copy it to clipboard
    dummy[0].select();
    document.execCommand('Copy');
    dummy.remove();

    // Show visual hint using a deferred (this way we don't need to
    // duplicate the functionality in the done() handler)
    (new Promise((resolve, reject) => {
        var confirmation = $('<div class="copyable-link-confirmation">');
        confirmation.text('Link wurde kopiert'.toLocaleString());
        confirmation.insertBefore(this);

        $(this).parent().addClass('copyable-link-animation');

        // Resolve deferred when animation has ended or after 2 seconds as a
        // fail safe
        var timeout = setTimeout(() => {
            $(this).parent().off('animationend');
            resolve(confirmation);
        }, 1500);
        $(this).parent().one('animationend', () => {
            clearTimeout(timeout);
            resolve(confirmation);
        });
    })).then((confirmation, parent) => {
        confirmation.remove();
        $(this).parent().removeClass('copyable-link-animation');
    });
});
