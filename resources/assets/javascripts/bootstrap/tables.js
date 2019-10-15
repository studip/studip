/*jslint esversion: 6*/

STUDIP.domReady(function() {
    if (window.MutationObserver !== undefined) {
        var observer = new window.MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    if (
                        $(mutation.target)
                            .attr('class')
                            .indexOf('open') !== -1
                    ) {
                        $(mutation.target)
                            .next()
                            .find('td')
                            .slideDown()
                            .find('.detailscontainer')
                            .hide()
                            .slideDown();
                    } else {
                        $(mutation.target)
                            .next()
                            .show()
                            .find('td')
                            .slideUp()
                            .find('.detailscontainer')
                            .slideUp();
                    }
                }
            });
        });
        $('table.withdetails > tbody > tr:not(.details)').each(function(index, element) {
            observer.observe(element, { attributes: true });
        });
    }
});

STUDIP.ready(function (event) {
    $('table.sortable-table:not(.tablesorter)', event.target).each((index, element) => {
        STUDIP.Table.enhanceSortableTable(element);
    });
});
