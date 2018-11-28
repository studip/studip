import "tablesorter/dist/js/jquery.tablesorter"
import "tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"
import "tablesorter/dist/js/jquery.tablesorter.widgets.js"

jQuery.tablesorter.addParser({
    id: 'htmldata',
    is: function (s, table, cell, $cell) {
        var c = table.config,
            p = c.parserMetadataName || 'sortValue';
        return $(cell).data(p) !== undefined;
    },
    format: function (s, table, cell) {
        var c = table.config,
            p = c.parserMetadataName || 'sortValue';
        return $(cell).data(p);
    },
    type: 'numeric'
});
