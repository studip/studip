$(document).on('click', 'a.copyable-link', function(event) {
    event.preventDefault();

    // Create dummy element and position it off screen
    // This element must be "visible" (as in "not hidden") or otherwise
    // the copy command will fail
    var dummy = $('<textarea>')
        .val(this.href)
        .css({
            position: 'absolute',
            left: '-9999px'
        })
        .appendTo('body');

    // Select text and copy it to clipboard
    dummy[0].select();
    document.execCommand('Copy');
    dummy.remove();

    // Show visual hint using a deferred (this way we don't need to
    // duplicate the functionality in the done() handler)
    $.Deferred(
        function(dfd) {
            var parent = $(this).parent(),
                confirmation = $('<div class="copyable-link-confirmation">');
            confirmation.text('Link wurde kopiert'.toLocaleString());
            confirmation.insertBefore(this);

            parent.addClass('copyable-link-animation');

            // Resolve deferred when animation has ended (if available) or
            // after 1 second if animations are not available
            if ($('html').hasClass('cssanimations')) {
                parent.on('animationend', function() {
                    dfd.resolveWith(this, [confirmation, parent]);
                });
            } else {
                setTimeout(
                    function() {
                        dfd.resolveWith(this, [confirmation, parent]);
                    }.bind(this),
                    1000
                );
            }
        }.bind(this)
    ).done(function(confirmation, parent) {
        confirmation.remove();
        parent.removeClass('copyable-link-animation');
    });
});
