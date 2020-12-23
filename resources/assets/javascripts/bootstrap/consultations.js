import { $gettext } from '../lib/gettext.js';

$(document).on('click', '.consultation-delete-check:not(.ignore)', event => {
    const form       = $(event.target).closest('form');
    const checkboxes = form.find(':checkbox[name="slot-id[]"]:checked');
    const ids        = checkboxes.map((index, element) => element.value.split('-').pop()).get();

    if (!ids.length) {
        return false;
    }

    STUDIP.api.GET('consultations/slots/bulk', {data: {ids: ids}}).done(slots => {
        let bookings = 0;
        slots.forEach(slot => bookings += slot.booking_count);
        if (bookings === 0) {
            STUDIP.Dialog.confirm($gettext('Wollen Sie diese Termine wirklich löschen?')).done(() => {
                $('<input type="hidden" name="delete" value="1"/>').appendTo(form);
                form.submit();
            });
        } else {
            $(event.target).addClass('ignore').click().removeClass('ignore');
        }
    });

    event.preventDefault();
});
