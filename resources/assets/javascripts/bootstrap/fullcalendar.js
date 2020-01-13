/*jslint esversion: 6*/
STUDIP.ready(function () {
    //Check if fullcalendar instances are to be displayed:
    $('*[data-fullcalendar="1"]').each(function () {
        STUDIP.loadChunk('fullcalendar').then(() => {
            if (this.calendar === undefined) {
                let calendar;
                if ($(this).hasClass('semester-plan')) {
                    calendar = STUDIP.Fullcalendar.createSemesterCalendarFromNode(this);
                } else {
                    calendar = STUDIP.Fullcalendar.createFromNode(this);
                }

                let continuousRefresh = (ttl) => {
                    setTimeout(() => {
                        calendar.updateSize();
                        if (ttl > 0) {
                            continuousRefresh(ttl - 1);
                        }
                    }, 200);
                };
                continuousRefresh(10);
            }
        });
    });

    if ($('#event-color-picker > option').length <= 1) {
        var selectedColor = $('#selected-color').val();
        var colors = ['yellow', 'orange', 'red', 'violet', 'dark-violet', 'green', 'dark-green', 'petrol', 'brown'];

        var style = window.getComputedStyle(document.body);
        colors.forEach(color => {
            let real_color = style.getPropertyValue(`--${color}`).trim();
            $('#event-color-picker').append([
                $('<input type="radio" name="event_color">').attr({
                    id: color,
                    value: real_color,
                    checked: selectedColor === real_color
                }),
                $('<label>').attr('for', color).css({
                    backgroundColor: `var(--${color})`
                }),
            ]);
        });
    }

});
