function enhanceSortableTable(table) {
    var headers = {};
    $('thead tr:last th', table).each(function(index, element) {
        headers[index] = {
            sorter: $(element).data().sort || false
        };
    });

    if ($('tbody tr[data-sort-fixed]', table).length > 0) {
        $('tbody tr[data-sort-fixed]', table).each(function() {
            $(this).data('sort-fixed', {
                index: $(this).index(),
                tbody: $(this).closest('table').find('tbody').index($(this).parent())
            });
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
                        var pos = $(this).data('sort-fixed');
                        if ($(`tbody:eq(${pos.tbody}) tr:eq(${pos.index})`, table).length > 0) {
                            $(`tbody:eq(${pos.tbody}) tr:eq(${pos.index})`, table).before(this);
                        } else {
                            $(`tbody:eq(${pos.tbody})`, table).append(this);
                        }

                        if ($(this).data('sort-hidden')) {
                            setTimeout(() => $(this).hide(), 100);
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
    enhanceSortableTable: function (table) {
        STUDIP.loadChunk('tablesorter').then(() => enhanceSortableTable(table));
    }
};

export default Table;
