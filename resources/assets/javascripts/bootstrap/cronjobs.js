// Cron task: Change tbody class according to inherent input setting
$(document).on('change', '.cron-task input', function() {
    $(this)
        .closest('tbody')
        .addClass('selected')
        .siblings()
        .removeClass('selected');
});

// Cron item:
// Display the following element and focus it's inherent input element
// if no value from a select element has been chosen. Hide the following
// element if a value has been chosen.
$(document).on('change', '.cron-item select', function() {
    var state = $(this).val().length > 0,
        $next = $(this).next();

    if (state) {
        $next
            .show()
            .find('input')
            .focus();
    } else {
        $next.hide();
    }
});

// Active date and time picker as well as the Cron item selector on
// document ready / page load.
STUDIP.domReady(function() {
    $('.cronjobs-edit input.has-date-picker').datepicker();
    $('.cronjobs-edit input.has-time-picker').timepicker();

    $('.cron-item select').change();
    $('.cronjobs tfoot select').change();
});
