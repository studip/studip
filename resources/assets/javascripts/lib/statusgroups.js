const Statusgroups = {
    ajax_endpoint: false,
    apply: function() {
        $('.movable tbody').sortable({
            axis: 'y',
            handle: '.dragHandle',
            helper: function(event, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            },
            start: function(event, ui) {
                $(this)
                    .closest('table')
                    .addClass('nohover');
            },
            stop: function(event, ui) {
                var table = $(this).closest('table'),
                    group = table.attr('id'),
                    user = ui.item.data('userid'),
                    position = $(ui.item).prevAll().length;

                table.removeClass('nohover');

                $.ajax({
                    type: 'POST',
                    url: Statusgroups.ajax_endpoint,
                    dataType: 'html',
                    data: { group: group, user: user, pos: position },
                    async: false
                }).done(function(data) {
                    $('tbody', table).html(data);
                    Statusgroups.apply();
                });
            }
        });
    },

    initInputs: function() {
        //$('input[name="selfassign_start"]').datetimepicker();
        if (!$('input[name="selfassign"]').attr('checked')) {
            $('input[name="exclusive"]')
                .closest($('section'))
                .hide();
            $('input[name="selfassign_start"]')
                .closest($('section'))
                .hide();
            $('input[name="selfassign_end"]')
                .closest($('section'))
                .hide();
        }
        //$('input[name="selfassign_end"]').datetimepicker();
        $('input[name="selfassign"]').on('click', function() {
            $('input[name="exclusive"]')
                .closest($('section'))
                .toggle();
            $('input[name="selfassign_start"]')
                .closest($('section'))
                .toggle();
            $('input[name="selfassign_end"]')
                .closest($('section'))
                .toggle();
        });

        $('input[name="numbering_type"]').on('click', function() {
            var type = $('input[name="numbering_type"]:checked').val(),
                disabled = parseInt(type, 10) === 2;

            $('input[name="startnumber"]')
                .prop('disabled', disabled)
                .toggle(!disabled);
        });
    }
};

export default Statusgroups;
