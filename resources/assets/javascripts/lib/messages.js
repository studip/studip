import Markup from './markup.js';

const Messages = {
    /*********** AJAX-reload function for overview ***********/

    periodicalPushData: function() {
        if (jQuery('#messages').length && jQuery('#since').val()) {
            return {
                since: jQuery('#since').val(),
                received: jQuery('#received').val(),
                tag: jQuery('#tag').val()
            };
        }
    },
    newMessages: function(response) {
        jQuery.each(response.messages, function(message_id, message) {
            if (jQuery('#message_' + message_id).length === 0) {
                jQuery('#messages > tbody').prepend(message);
            }
        });
        jQuery('#since').val(Math.floor(new Date().getTime() / 1000));
    },

    /*********** helper for the overview site ***********/

    whenMessageIsShown: function(lightbox) {
        jQuery(lightbox)
            .closest('tr')
            .removeClass('unread');
    },

    /*********** helper for the composer-site ***********/

    add_adressee: function(user_id, name) {
        var new_adressee = jQuery('#template_adressee').clone();
        new_adressee.find('input').val(user_id);
        new_adressee
            .find('.visual')
            .html(name)
            .find('b')
            .replaceWith(function() {
                return jQuery(this).contents();
            });
        new_adressee.find('img.avatar-medium').remove();
        new_adressee.find('br').replaceWith(' ');
        new_adressee
            .removeAttr('id')
            .appendTo('#adressees')
            .fadeIn();
        return false;
    },

    add_adressees: function(form) {
        jQuery(form)
            .find('#add_adressees_selectbox option:selected')
            .each(function() {
                var user_id = jQuery(this).val(),
                    name = jQuery(this).text();

                var new_adressee = jQuery('#template_adressee').clone();
                new_adressee.find('input').val(user_id);
                new_adressee.find('.visual').text(name);
                new_adressee
                    .removeAttr('id')
                    .appendTo('#adressees')
                    .fadeIn();
            });
        jQuery(form)
            .closest('.ui-dialog-content')
            .dialog('close');
        return false;
    },

    remove_adressee: function() {
        jQuery(this)
            .closest('li')
            .fadeOut(300, function() {
                jQuery(this).remove();
            });
    },

    remove_attachment: function() {
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/messages/delete_attachment',
            data: {
                document_id: jQuery(this)
                    .closest('li')
                    .data('document_id'),
                message_id: jQuery(this)
                    .closest('form')
                    .find('input[name=message_id]')
                    .val()
            },
            type: 'POST'
        });
        jQuery(this)
            .closest('li')
            .fadeOut(300, function() {
                jQuery(this).remove();
            });
    },

    upload_from_input: function(input) {
        Messages.upload_files(input.files);
        jQuery(input).val('');
    },
    fileIDQueue: 1,
    upload_files: function(files) {
        for (var i = 0; i < files.length; i++) {
            var fd = new FormData();
            fd.append('file', files[i], files[i].name);
            var statusbar = jQuery('#statusbar_container .statusbar')
                .first()
                .clone()
                .show();
            statusbar.appendTo('#statusbar_container');
            fd.append('message_id', jQuery('#message_id').val());
            Messages.upload_file(fd, statusbar);
        }
    },
    upload_file: function(formdata, statusbar) {
        $(".ui-dialog-buttonset button:first-child, footer[data-dialog-button] button:first-child").attr("disabled", "disabled");
        $.ajax({
            xhr: function() {
                var xhrobj = $.ajaxSettings.xhr();
                if (xhrobj.upload) {
                    xhrobj.upload.addEventListener(
                        'progress',
                        function(event) {
                            var percent = 0;
                            var position = event.loaded || event.position;
                            var total = event.total;
                            if (event.lengthComputable) {
                                percent = Math.ceil((position / total) * 100);
                            }
                            //Set progress
                            statusbar.find('.progress').css({ 'min-width': percent + '%', 'max-width': percent + '%' });
                            statusbar
                                .find('.progresstext')
                                .text(percent === 100 ? jQuery('#upload_finished').text() : percent + '%');
                        },
                        false
                    );
                }
                return xhrobj;
            },
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/messages/upload_attachment',
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            data: formdata,
            dataType: 'json'
        })
            .done(function(data) {
                $(".ui-dialog-buttonset button:first-child, footer[data-dialog-button] button:first-child").removeAttr("disabled");
                statusbar.find('.progress').css({ 'min-width': '100%', 'max-width': '100%' });
                var file = jQuery('#attachments .files > .file')
                    .first()
                    .clone();
                file.find('.name').text(data.name);
                if (data.size < 1024) {
                    file.find('.size').text(data.size + 'B');
                }
                if (data.size > 1024 && data.size < 1024 * 1024) {
                    file.find('.size').text(Math.floor(data.size / 1024) + 'KB');
                }
                if (data.size > 1024 * 1024 && data.size < 1024 * 1024 * 1024) {
                    file.find('.size').text(Math.floor(data.size / 1024 / 1024) + 'MB');
                }
                if (data.size > 1024 * 1024 * 1024) {
                    file.find('.size').text(Math.floor(data.size / 1024 / 1024 / 1024) + 'GB');
                }
                file.find('.icon').html(data.icon);
                file.data('document_id', data.document_id);
                file.appendTo('#attachments .files');
                file.fadeIn(300);
                statusbar.find('.progresstext').text(jQuery('#upload_received_data').text());
                statusbar.delay(1000).fadeOut(300, function() {
                    jQuery(this).remove();
                });
            })
            .fail(function(jqxhr, status, errorThrown) {
                var error = jqxhr.responseJSON.error;

                statusbar
                    .find('.progress')
                    .addClass('progress-error')
                    .attr('title', error);
                statusbar.find('.progresstext').html(error);
                statusbar.on('click', function() {
                    jQuery(this).fadeOut(300, function() {
                        jQuery(this).remove();
                    });
                });
            });
    },
    checkAdressee: function() {
        // Check if recipients added (one element is always there -> template)
        var quicksearch = jQuery('form[name="write_message"] input[name="user_id_parameter"]');
        if (jQuery('li.adressee').children('input[name^="message_to"]').length <= 1) {
            quicksearch.attr('required', 'required').attr('value', '');
            quicksearch[0].setCustomValidity(
                'Sie haben nicht angegeben, wer die Nachricht empfangen soll!'.toLocaleString()
            );
            return true;
        } else {
            quicksearch.removeAttr('required');
            quicksearch[0].setCustomValidity('');
            return true;
        }
    },
    setTags: function(message_id, tags) {
        var container = jQuery('#message_' + message_id)
                .find('.tag-container')
                .empty(),
            template = _.template('<a href="<%- url %>" class="message-tag"><%- tag %></a>');

        jQuery.each(tags, function(index, tag) {
            var html = template({
                url: STUDIP.URLHelper.getURL('dispatch.php/messages/overview', { tag: tag }),
                tag: tag.charAt(0).toUpperCase() + tag.slice(1) // ucfirst
            });
            jQuery(container)
                .append(html)
                .append(' ');
        });
    },
    setAllTags: function(tags) {
        var container = $('#messages-tags ul');
        var template = _.template('<li><a href="<%- url %>" class="tag"><%- tag %></a></li>');

        container.children('li:not(:has(.all-tags))').remove();

        jQuery.each(tags, (index, tag) => {
            let html = template({
                url: STUDIP.URLHelper.getURL('dispatch.php/messages/overview', { tag: tag }),
                tag: tag.charAt(0).toUpperCase() + tag.slice(1) // ucfirst
            });
            $(container).append(html);
        });
        $('#messages-tags')
            .toggle(tags.length !== 0)
            .find('li:has(.tag):not(.ui-droppable)')
            .each(Messages.createDroppable);
    },
    createDroppable: function(element) {
        jQuery(arguments.length === 1 ? element : this).droppable({
            hoverClass: 'dropping',
            drop: function(event, ui) {
                var message_id = ui.draggable.attr('id').substr(ui.draggable.attr('id').lastIndexOf('_') + 1),
                    tag = jQuery(this)
                        .text()
                        .trim();
                jQuery
                    .post(STUDIP.URLHelper.getURL('dispatch.php/messages/tag/' + message_id), {
                        add_tag: tag
                    })
                    .then(function(response, status, xhr) {
                        var tags = jQuery.parseJSON(xhr.getResponseHeader('X-Tags'));
                        Messages.setTags(message_id, tags);
                    });
            }
        });
    },
    toggleSetting: function(name) {
        jQuery('#' + name).toggle('fade');
        if (jQuery('#' + name).is(':visible')) {
            jQuery('#' + name)[0].scrollIntoView(false);
        }
    },
    previewComposedMessage: function() {
        var old_written_text = '',
            written_text = jQuery('textarea[name=message_body]').val();
        var updatePreview = function() {
            written_text = jQuery('textarea[name=message_body]').val();
            if (old_written_text !== written_text) {
                jQuery.ajax({
                    url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/messages/preview',
                    data: {
                        text: STUDIP.editor_enabled ? STUDIP.wysiwyg.markAsHtml(written_text) : written_text
                    },
                    type: 'POST',
                    success: function(html) {
                        jQuery('#preview .message_body').html(html);
                        Markup.element('#preview .message_body');
                    }
                });
                old_written_text = written_text;
            }
            if (jQuery('#preview .message_body').is(':visible')) {
                window.setTimeout(updatePreview, 1000);
            }
        };
        updatePreview();
    }
};

export default Messages;
