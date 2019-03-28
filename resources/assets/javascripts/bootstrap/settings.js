// Copy elements value to another element on change
// Used for title choosers
$(document).on('change', '[data-target]', function() {
    var target = $(this).data().target;
    $(target).val(this.value);
});

STUDIP.domReady(() => {
    $('#edit_userdata').on('change', 'input[name^=email]', function() {
        var changed = false;
        $('#edit_userdata input[name^=email]').each(function() {
            changed = changed || this.value !== this.defaultValue;
        });
        $('#edit_userdata .email-change-confirm').toggle(changed);
    });

    $('#edit_userdata .email-change-confirm').hide();
});

//
$(document).on('change', '#settings-notifications :checkbox', function() {
    var name = $(this).attr('name');

    if (name === 'all[all]') {
        $(this)
            .closest('table')
            .find(':checkbox')
            .prop('checked', this.checked);
        return;
    }

    if (/all\[columns\]/.test(name)) {
        var index =
            $(this)
                .closest('td')
                .index() + 2;
        $(this)
            .closest('table')
            .find('tbody td:nth-child(' + index + ') :checkbox')
            .prop('checked', this.checked);
    } else if (/all\[rows\]/.test(name)) {
        $(this)
            .closest('td')
            .siblings()
            .find(':checkbox')
            .prop('checked', this.checked);
    }

    $('.notification.settings tbody :checkbox[name^=all]').each(function() {
        var other = $(this)
            .closest('td')
            .siblings()
            .find(':checkbox');
        this.checked = other.filter(':not(:checked)').length === 0;
    });

    $('.notification.settings thead :checkbox').each(function() {
        var index =
                $(this)
                    .closest('td')
                    .index() + 2,
            other = $(this)
                .closest('table')
                .find('tbody td:nth-child(' + index + ') :checkbox');
        this.checked = other.filter(':not(:checked)').length === 0;
    });
});
