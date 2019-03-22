$(document).ready(function() {
    if ($('.sem-tree-assigned-root > ul > li').length == 0) {
        $('.sem-tree-assigned-root').addClass('hidden-js');
    }
});

$(document).on('studip-ready', function() {
    $('.course-wizard-step-0 *:input:not(input[type=submit])').each(function (index) {
        $(this).attr(
            'tabindex',
            $(this).closest('section,footer').css('order')
        );
    });
});
