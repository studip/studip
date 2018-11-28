CKEDITOR.dialog.add('settingsDialog', function (editor) {
    var lang = editor.lang['studip-settings'];

    // Time span after which the UI will display an abort option
    // to the user if the HTTP request hasn't yet finished.
    var uiTimeout = 3000;

    var settings = {
        url: STUDIP.URLHelper.resolveURL(
            'dispatch.php/wysiwyg/settings/users/current'
        ),
        save: function (data) {
            return $.ajax({
                url: this.url,
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data)
            });
        }
    };

    var
    dialog = null,
    status = $('<span>').attr('class', 'cke_disabled'),
    saveEvents = 0; // remember how many save events are currently active

    function save(data) {
        status.html(lang.savingChanges);
        saveEvents++;

        dialog.disableButton('ok');

        var request = settings.save(data);

        var timeoutId = setTimeout(function () {
            status
            .append(' (')
            .append(
                $('<a>')
                .text(lang.abort)
                .css('text-decoration', 'underline') // TODO use default styles
                .click(function (event) {
                    event.preventDefault();
                    request.abort();
                })
            )
            .append(')');
        }, uiTimeout);

        request
        .done(function () {
            status.html(lang.savedChanges);
        })
        .fail(function (xhr) {
            var $error = $('<a>')
            .text(lang.information)
            .css('text-decoration', 'underline') // TODO use default styles
            .click(function (event) {
                event.preventDefault();
                alert(
                    settings.url +
                    '\n\n' + lang.status + ' ' + xhr.status +
                    ' ' + xhr.statusText +
                    '\n' + lang.response + ' ' + xhr.responseText
                );
            });

            status
            .html(lang.savingFailed + ' (')
            .append($error)
            .append(')');
        })
        .always(function () {
            clearTimeout(timeoutId);
            saveEvents--;
            if (saveEvents <= 0) {
                dialog.enableButton('ok');
            }
        });

        return request;
    }

    return {
        title: lang.dialogTitle,
        width: 400,
        height: 200,
        resizable: CKEDITOR.DIALOG_RESIZE_NONE,
        contents: [{
            elements: [{
                type: 'checkbox',
                id: 'disable',
                label: lang.disableEditorLabel,
                onClick: function() {
                    var checkbox = this;
                    checkbox.disable(); // prevent multiple save events

                    save({ disabled: checkbox.getValue() })
                    .done(function (settings) {
                        checkbox.setValue(settings.disabled);
                    })
                    .fail(function () {
                        checkbox.setValue(!checkbox.getValue());
                    })
                    .always(function () {
                        checkbox.enable();
                    });
                }
            }, {
                type: 'html',
                style: 'white-space: normal',
                html: lang.disableEditorInfo
            }]
        }],
        onLoad: function (event) {
            $(this.parts.footer.$).append(status);
            dialog = this;
        },
        onShow: function (event) {
            status.text('');
        }
    };
});

