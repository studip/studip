/* ------------------------------------------------------------------------
 * Multiselect
 * ------------------------------------------------------------------------ */

/**
 * Turns a select-box into an easy to use multiple select-box
 */
const MultiSelect = {
    /**
     * @param id string:
     */
    create: function(id, itemName) {
        if (!jQuery(id).attr('multiple')) {
            jQuery(id)
                .attr('multiple', 'multiple')
                .css('height', '120px');
        }
        jQuery(id).multiselect({
            sortable: false,
            draggable: true,
            dividerLocation: 0.5,
            itemName: itemName
        });
    }
};

export default MultiSelect;
