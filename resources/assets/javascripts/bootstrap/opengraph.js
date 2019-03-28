function handleOpenGraphSections() {
    $('.opengraph-area:not(.handled)').each(function() {
        var items = $('.opengraph', this),
            switcher;
        if (items.length > 1) {
            items.filter(':gt(0)').addClass('hidden');

            switcher = $('<ul class="switcher">');
            $('<li><button class="switch-left" disabled>&lt;</button></li>').appendTo(switcher);
            $('<li><button class="switch-right">&gt;</button></li>').appendTo(switcher);
            switcher.prependTo(this);
        }

        $(this).addClass('handled');
    });
}

STUDIP.ready(handleOpenGraphSections);
$(document).on('ajaxComplete', handleOpenGraphSections);

$(document).on('click', '.opengraph-area .switcher button', function (event) {
    var direction = $(this).is('.switch-left') ? 'left' : 'right',
        current = $(this)
            .closest('.opengraph-area')
            .find('.opengraph:visible'),
        switcher = $(this).closest('.switcher'),
        buttons = {
            left: $('.switch-left', switcher),
            right: $('.switch-right', switcher)
        };

    if (direction === 'left') {
        current = current
            .addClass('hidden')
            .prev()
            .removeClass('hidden');
        buttons.left.attr('disabled', current.prev('.opengraph').length === 0);
        buttons.right.attr('disabled', false);
    } else {
        current = current
            .addClass('hidden')
            .next()
            .removeClass('hidden');
        buttons.left.attr('disabled', false);
        buttons.right.attr('disabled', current.next('.opengraph').length === 0);
    }

    event.preventDefault();
}).on('click', '.opengraph a.flash-embedder', function (event) {
    let url = $(this).attr('href');
    let template = _.template('<iframe width="100%" height="200px" frameborder="0" src="<%= url %>"></iframe>');
    $(this).replaceWith(template({ url: url }));

    event.preventDefault();
});
