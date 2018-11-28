$(function() {
    if (
        $('.sem-tree-assigned-root')
            .children('ul')
            .children('li').length == 0
    ) {
        $('.sem-tree-assigned-root').addClass('hidden-js');
    }
});

$(document).on('ready dialog-update', function() {
    $('.course-wizard-step-0 *:input:not(input[type=submit])').each(function(index) {
        $(this).attr(
            'tabindex',
            $(this)
                .closest('section,footer')
                .css('order')
        );
    });
});
