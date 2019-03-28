/**
 * This file contains all wiki related javascript.
 *
 * For now this is the "submit and edit" functionality via ajax.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright Stud.IP Core Group
 * @license   GPL2 or any later version
 * @since     Stud.IP 3.3
 */

$(document).on('click', '#wiki button[name="submit-and-edit"]', function(event) {
    var form = $(this).closest('form'),
        data = {},
        form_data,
        i,
        id,
        wysiwyg_editor = false;

    if (STUDIP.editor_enabled) {
        id = $('textarea[name="body"]', form).attr('id');
        wysiwyg_editor = CKEDITOR.instances[id];
        wysiwyg_editor.setData(STUDIP.wysiwyg.markAsHtml(wysiwyg_editor.getData()));
        wysiwyg_editor.updateElement();
    }

    form_data = form.serializeArray();

    // Show ajax overlay to indicate activity (and prevent buttons to be
    // clicked again)
    STUDIP.Overlay.show(true, form.css('position', 'relative'));

    // Include this button into form's data
    form_data.push({
        name: $(this).attr('name'),
        value: true
    });

    // Transform data into an easier accessible format
    for (i = 0; i < form_data.length; i += 1) {
        data[form_data[i].name] = form_data[i].value;
    }

    // Check version
    $.getJSON(
        STUDIP.URLHelper.getURL('dispatch.php/wiki/version_check/' + data.version, {
            keyword: data.wiki
        })
    )
        .then(function(response, status, jqxhr) {
            var error = jqxhr.getResponseHeader('X-Studip-Error'),
                to_confirm = jqxhr.getResponseHeader('X-Studip-Confirm'),
                confirmed = false;
            // Unrecoverable error
            if (response === false) {
                window.alert(error);
                return;
            }
            // Saving needs confirmation (newer version available?)
            if (response === null) {
                confirmed = window.confirm(error + '\n\n' + to_confirm);
            } else {
                confirmed = true;
            }
            // Ready to save
            if (confirmed) {
                $.ajax({
                    type: (form.attr('method') || 'GET').toUpperCase(),
                    url: STUDIP.URLHelper.getURL('dispatch.php/wiki/store/' + data.version),
                    data: {
                        keyword: data.wiki,
                        body: data.body
                    },
                    dataType: 'json'
                }).then(function(response) {
                    var textarea = $('textarea[name=body]', form);

                    // Update header info containing version and author
                    $(form)
                        .closest('table')
                        .prev('table')
                        .find('td:last-child')
                        .html(response.zusatz);

                    // Update version field
                    $('input[type=hidden][name=version]', form).val(response.version);

                    if (wysiwyg_editor) {
                        wysiwyg_editor.setData(response.body);
                    } else {
                        // Store current selection/caret position
                        textarea.storeSelection();

                        // Update textarea, restore selection/caret position
                        textarea.val(response.body);
                        textarea.prop('defaultValue', textarea.val());
                        textarea.restoreSelection();
                        textarea.change();
                        textarea.focus();
                    }

                    // Remove messages (and display new messages, if any)
                    $('#layout_content .messagebox').remove();
                    if (response.messages !== false) {
                        $(response.messages).prependTo('#layout_content');
                    }
                });
            }
        })
        .always(function() {
            // Always hide overlay when ajax request is complete
            STUDIP.Overlay.hide();
        });

    event.preventDefault();
});

$(document).on('keyup change', '#wiki textarea[name=body]', function() {
    // Disable "save and edit" button if text was not changed
    $('#wiki button[name="submit-and-edit"]').prop('disabled', this.value === this.defaultValue);
});

STUDIP.domReady(() => {
    if (!STUDIP.editor_enabled) {
        // Trigger above disable mechanism only when not using wysiwyg
        $('#wiki textarea[name=body]').change();
    } else {
        $(document).off('keyup change', '#wiki textarea[name=body]');
    }
});

$(document).on('change', '#wiki-config .global-permissions :checkbox', function () {
    if ($(this).is(':checked')) {
        return;
    }

    $('#wiki-config .read-permissions [data-activates],[data-deactivates]').filter(':checked').change();
}).on('change', '#wiki-config .read-permissions :radio', function () {
    $('#wiki-config .edit-permissions:has(:radio[disabled]:checked) :radio:not([disabled]):first').prop('checked', true);
});
