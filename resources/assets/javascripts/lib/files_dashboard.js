import Table from './table.js';

const FilesDashboard = {
    /**
     * Diese Methode wird aufgerufen, sobald ein Dashboard-Widget
     * maximiert wurde. Die dort enthaltene Tabelle wird dann
     * sortierbar gemacht.
     * Die `elementId` bezieht sich auf die widget_element_id des Widgets.
     */
    enhanceList: function(elementId) {
        $(document).on('dialog-open', function() {
            $('.ui-dialog table[data-element-id="' + elementId + '"]').each(function(index, element) {
                Table.enhanceSortableTable(element);
            });
        });
    }
};

export default FilesDashboard;
