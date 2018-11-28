jQuery(document).on('click', 'td.calendar-day-edit, td.calendar-day-event', function(event) {
    var elem = jQuery(this)
        .find('a')
        .first();
    if (_.isString(elem.attr('href'))) {
        STUDIP.Dialog.fromURL(elem.attr('href'), { title: elem.attr('title') });
        event.preventDefault();
    } else {
        return false;
    }
});
