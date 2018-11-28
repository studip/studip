const Raumzeit = {
    disableBookableRooms: function(icon) {
        var select = $(icon).prev('select')[0];
        var me = $(icon);
        select.title = '';
        $(select)
            .children('option')
            .each(function() {
                $(this).prop('disabled', false);
            });

        me.attr('data-state', false);
        me.attr('title', 'Nur buchbare Räume anzeigen'.toLocaleString());
    }
};

export default Raumzeit;
