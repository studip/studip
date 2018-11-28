import Dialog from './dialog.js';

const Instschedule = {
    /**
     * show the details of a grouped-entry in the isntitute-calendar, containing several seminars
     *
     * @param  string  the id of the grouped-entry to be displayed
     */
    showInstituteDetails: function(id) {
        jQuery.get(STUDIP.URLHelper.getURL('dispatch.php/calendar/instschedule/groupedentry/' + id), function(data) {
            Dialog.show(data, {
                title: 'Detaillierte Veranstaltungsliste'.toLocaleString()
            });
        });
    }
};

export default Instschedule;
