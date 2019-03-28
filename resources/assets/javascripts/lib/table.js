function enhanceSortableTable(table) {
    var headers = {};
    $('thead tr:last th', table).each(function(index, element) {
        headers[index] = {
            sorter: $(element).data().sort || false
        };
    });

    if ($('tbody tr[data-sort-fixed]', table).length > 0) {
        $('tbody tr[data-sort-fixed]', table).each(function() {
            var index = $(this).index();
            $(this).data('sort-fixed', index);
        });
        $(table)
            .on('sortStart', function() {
                $('tbody tr[data-sort-fixed]', table).each(function() {
                    var hidden = $(this).is(':hidden');
                    $(this).data('sort-hidden', hidden);
                });
            })
            .on('sortEnd', function() {
                $('tbody tr[data-sort-fixed]', table)
                    .detach()
                    .each(function() {
                        var index = $(this).data('sort-fixed');
                        if ($('tbody tr', table).length === 0) {
                            $('tbody:first', table).append(this);
                        } else {
                            $('tbody tr:eq(' + index + ')', table).before(this);
                        }

                        if ($(this).data('sort-hidden')) {
                            setTimeout(
                                function() {
                                    $(this).hide();
                                }.bind(this),
                                100
                            );
                        }
                    });
            });
    }

    $(table).tablesorter({
        headers: headers,
        sortLocaleCompare : true,
        sortRestart: true
    });
}

const Table = {
    enhanceSortableTable: function(table) {
        // async load the tablesorter, then enhance
        import(/* webpackChunkName: "tablesorter" */ '../chunks/tablesorter')
            .then(function() {
                enhanceSortableTable(table);
            })
            .catch(function(error) {
                console.error('An error occurred while loading the tablesorter component', error);
            });
    }
};

export default Table;
