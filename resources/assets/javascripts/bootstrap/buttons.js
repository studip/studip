STUDIP.domReady(() => {
    jQuery(document).on('click', 'button.button.copy-to-clipboard', function(event) {
        var text_to_copy = jQuery(event.target).data('copy');
        if (navigator.clipboard == undefined) {
            //Copy via document.exec and a hidden input:
            var hidden_input = jQuery('<input type="hidden">');
            jQuery(hidden_input).val(text_to_copy);
            jQuery(event.target).append(hidden_input);
            jQuery(hidden_input).select();
            document.exec('copy');
            //Remove the hidden input:
            jQuery(hidden_input).remove();
        } else {
            //Use the Clipboard API:
            navigator.clipboard.writeText(text_to_copy);
        }
    });
});
