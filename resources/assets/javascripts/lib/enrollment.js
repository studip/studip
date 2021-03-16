export default function enrollment() {
    /**
     * Filter logic for courses on both sides
     */
    $('#enrollment').on('keyup', 'input[name="filter"]', function () {
        var text = $(this).val().trim(),
            list = $(this).next('ul');

        if (text.length > 0) {
            var exp = new RegExp(text, 'gi');

            list.children('li').each(function() {
                var name = $(this).text();
                $(this).toggle(name.search(exp) !== -1);
            });
        } else {
            list.children('li:not(.empty)').show();
        }
    }).on('click', '.actions input, .button input', function () {
        var action = $(this).closest('form').attr('action'),
            data   = $(this).closest('form').serializeArray();

        data.push({name: this.name, value: this.value});

        STUDIP.Overlay.show(true, null, null, null, 300);

        $.post(action, data).done(function (response) {
            var enrollment = $('#enrollment', response);
            $('#enrollment').html(enrollment);
        }).always(function () {
            STUDIP.Overlay.hide();
        });

        return false;
    });

    // Disable drag and drop features for small displays
    if (!$('html').hasClass('size-medium')) {
        return;
    }

    /**
     * Allow courses to be sorted via drag and drop according to their
     * priorities.
     */
    $('#enrollment #selected-courses').sortable({
        appendTo: '#selected-courses',
        axis: 'y',
        cancel: 'li.empty,li:nth-child(2):last-child',
        cursor: 'move',
        items: 'li:not(.empty)',
        placeholder: 'ui-state-highlight',
        tolerance: 'pointer',

        helper: function (event, element) {
            return $(element).clone().width($(element).width()).css({
                overflow: 'hidden'
            });
        },
        receive: function (event, ui) {
            ui.helper.width('auto');
            ui.item.removeClass('visible');
        },
        update: function(event, ui) {
            // Adjust priority and add neccessary elements if missing
            $(this).find('li:not(.empty)').each(function (index) {
                var id = $(this).data().id,
                    hiddenElement = $(this).find('input[type="hidden"]');

                index += 1;

                if ($(this).find('.delete').length === 0) {
                    var delete_icon = $('script#delete-icon-template').html();
                    $(this).append(delete_icon);
                }

                if (hiddenElement.length === 0) {
                    $(this).append('<input type="hidden" name="admission_prio[' + id + ']" value="' + index + '">');
                    hiddenElement = $(this).find('input');
                }

                hiddenElement.val(index);
            });
        }
    }).on('click', '.delete', function() {
        var id = $(this).closest('li').remove().data().id;

        $('#available-courses [data-id="' + id + '"]').addClass('visible');

        $('#enrollment #selected-courses li:not(.empty)').each(function (index) {
            $(this).find('input[type="hidden"]').val(index + 1);
        });

        return false;
    }).disableSelection();

    /**
     * Allow courses to be dragged to the above defined sortable.
     */
    $('#enrollment #available-courses li').draggable({
        activeClass: 'ui-state-highlight',
        appendTo: '#available-courses',
        connectToSortable: '#selected-courses',
        containment: '#enrollment',
        cursor: 'move',

        helper: function () {
            return $(this).clone().width($(this).width());
        }
    }).disableSelection();
}
