/* ------------------------------------------------------------------------
 * Forms
 * ------------------------------------------------------------------------ */

const Forms = {
    initialized: false,
    initialize: function(scope) {
        if (scope === undefined) {
            scope = document;
        }

        $('input[required],textarea[required]', scope).attr('aria-required', true);
        $('input[pattern][title],textarea[pattern][title]', scope).each(function() {
            $(this).data('message', $(this).attr('title'));
        });

        if (!Forms.initialized) {
            // add invalid-handler to every input and textarea on the page
            $(document).on('invalid', 'input, textarea', function() {
                $(this)
                    .attr('aria-invalid', 'true')
                    .change(function() {
                        $(this).removeAttr('aria-invalid');
                    });

                // get the fieldset that contains the invalid input
                var fieldset = $(this).closest('fieldset');
                // toggle the collapsed class if the fieldset is currently collapsed
                if (fieldset.hasClass('collapsed')) {
                    fieldset.toggleClass('collapsed');
                }
            });

            $(document).on('change', 'form.default label.file-upload input[type=file]', function(ev) {
                var selected_file = ev.target.files[0],
                    filename;
                if (
                    $(this)
                        .closest('label')
                        .find('.filename').length
                ) {
                    filename = $(this)
                        .closest('label')
                        .find('.filename');
                } else {
                    filename = $('<span class="filename"/>');
                    $(this)
                        .closest('label')
                        .append(filename);
                }
                filename.text(selected_file.name + ' ' + Math.ceil(selected_file.size / 1024) + 'KB');
            });
        }

        Forms.initialized = true;
    }
};

export default Forms;
