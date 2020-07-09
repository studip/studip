/*jslint esversion: 6*/

import Dialog from './dialog.js';

const Files = {
    init () {
        if ($('#files-index, #course-files-index, #files-flat, #course-files-flat, #files-overview').length
            && jQuery("#files_table_form").length) {
            this.filesapp = new Vue({
                el: "#layout_container",
                data: {
                    "files":       jQuery("#files_table_form").data("files") || [],
                    "folders":     jQuery("#files_table_form").data("folders") || [],
                    "topfolder":   jQuery("#files_table_form").data("topfolder"),
                    "breadcrumbs": jQuery("#files_table_form").data("breadcrumbs") || []
                },
                methods: {
                    hasFilesOfType (type) {
                        for (let i in this.files) {
                            if (this.files[i].mime_type.indexOf(type) === 0) {
                                return true;
                            }
                        }
                        return false;
                    }
                }
            });
        }

        //The following is only for (read only) vue file tables where multiple
        //tables are displayed in one page.
        var tables = jQuery('.vue-file-table');
        if (tables.length) {
            for (var table of tables) {
                var vue_instance = new Vue({
                    el: table,
                    data: {
                        "files":       jQuery(table).data("files") || [],
                        "folders":     jQuery(table).data("folders") || [],
                        "topfolder":   jQuery(table).data("topfolder"),
                        "breadcrumbs": jQuery(table).data("breadcrumbs") || []
                    }
                });
            }
        }
    },
    openAddFilesWindow: function(folder_id) {
        var responsive_mode = jQuery('html').first().hasClass('responsive-display');
        if ($('.files_source_selector').length > 0) {
            Dialog.show($('.files_source_selector').html(), {
                title: 'Dokument hinzufügen'.toLocaleString(),
                size: (responsive_mode ? undefined : 'auto')
            });
        } else {
            Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/add_files_window/' + folder_id), {
                title: 'Dokument hinzufügen'.toLocaleString(),
                size: (responsive_mode ? undefined : 'auto')
            });
        }
    },
    validateUpload: function(file) {
        if (!Files.uploadConstraints) {
            return true;
        }
        if (file.size > Files.uploadConstraints.filesize) {
            return false;
        }
        var ending = file.name.lastIndexOf('.') !== -1 ? file.name.substr(file.name.lastIndexOf('.') + 1) : '';

        if (Files.uploadConstraints.type === 'allow') {
            return $.inArray(ending, Files.uploadConstraints.file_types) === -1;
        }

        return $.inArray(ending, Files.uploadConstraints.file_types) !== -1;
    },
    upload: function(filelist) {
        var files = 0,
            folder_id = $('.files_source_selector').data('folder_id'),
            data = new FormData();

        //Open upload-dialog
        $('.file_upload_window .filenames').html('');
        $('.file_upload_window .errorbox').hide();
        $('.file_upload_window .messagebox').hide();
        $.each(filelist, function(index, file) {
            if (Files.validateUpload(file)) {
                data.append('file[]', file, file.name);
                $('<li/>')
                    .text(file.name)
                    .appendTo('.file_upload_window .filenames');
                files += 1;
            } else {
                $('.file_upload_window .errorbox').show();
                $('.file_upload_window .errorbox .errormessage').text(
                    'Datei ist zu groß oder hat eine nicht erlaubte Endung.'.toLocaleString()
                );
            }
        });
        if ($('.file_uploader').length > 0) {
            Dialog.show($('.file_uploader').html(), {
                title: 'Datei hochladen'.toLocaleString()
            });
        } else {
            Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/upload_window'), {
                title: 'Datei hochladen'.toLocaleString()
            });
        }

        //start upload
        $('form.drag-and-drop.files').removeClass('hovered');
        if (files > 0) {
            $('.file_upload_window .uploadbar')
                .show()
                .css('background-size', '0% 100%');
            $.ajax({
                url: STUDIP.URLHelper.getURL('dispatch.php/file/upload/' + folder_id),
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener(
                            'progress',
                            function(event) {
                                var percent = 0,
                                    position = event.loaded || event.position,
                                    total = event.total;
                                if (event.lengthComputable) {
                                    percent = Math.ceil((position / total) * 100);
                                }
                                //Set progress
                                $('.file_upload_window .uploadbar').css('background-size', percent + '% 100%');
                            },
                            false
                        );
                    }
                    return xhr;
                }
            }).done(function (json) {
                $('.file_upload_window .uploadbar').css('background-size', '100% 100%');
            });
        } else {
            $('.file_upload_window .uploadbar').hide();
        }
    },
    addFile: (payload, delay = 0, hide_dialog = true) => {
        var redirect = false;
        var html = [];

        if (payload.hasOwnProperty('html') && payload.html !== undefined) {
            redirect = payload.redirect;
            html = payload.html;
        }

        if (redirect) {
            Dialog.fromURL(redirect);
        } else if (hide_dialog) {
            window.setTimeout(Dialog.close, 20);
        }

        if ($('table.documents').length > 0) {
            // on files page
            Files.addFileDisplay(html, delay);
        } else if (payload.url) {
            //not on files page

            Dialog.handlers.header['X-Location'](payload.url);
        }
    },
    addFileDisplay: (html, delay = 0) => {
        if (!Array.isArray(html)) {
            html = [html];
        }
        html.forEach((value, i) => {
            let insert = true;
            for (let i in STUDIP.Files.filesapp.files) {
                if (value.id == STUDIP.Files.filesapp.files[i].id) {
                    STUDIP.Files.filesapp.files[i] = value;
                    insert = false;
                }
            }
            if (insert) {
                STUDIP.Files.filesapp.files.push(value);
            }
        });
        $(document).trigger('refresh-handlers');
    },
    removeFileDisplay: function (ids) {
        if (!Array.isArray(ids)) {
            ids = [ids];
        }

        var count = ids.length;
        ids.forEach((id) => {
            STUDIP.Files.filesapp.removeFile(id);
        });
        $(document).trigger('refresh-handlers');
    },
    addFolderDisplay: function (html, delay = 0) {
        if (!Array.isArray(html)) {
            html = [html];
        }
        html.forEach((value, i) => {
            STUDIP.Files.filesapp.folders.push(value);
        });
        $(document).trigger('refresh-handlers');
    },
    getFolders: function(name) {
        var element_name = 'folder_select_' + name,
            context = $('#' + element_name + '-destination').val(),
            range = null;

        if ($.inArray(context, ['courses']) > -1) {
            range = $('#' + element_name + '-range-course > div > input')
                .first()
                .val();
        } else if ($.inArray(context, ['institutes']) > -1) {
            range = $('#' + element_name + '-range-inst > div > input')
                .first()
                .val();
        } else if ($.inArray(context, ['myfiles']) > -1) {
            range = $('#' + element_name + '-range-user_id').val();
        }

        if (range !== null) {
            $.post(
                STUDIP.URLHelper.getURL('dispatch.php/file/getFolders'),
                { range: range },
                function(data) {
                    if (data) {
                        $('#' + element_name + '-subfolder select').empty();
                        $.each(data, function(index, value) {
                            $.each(value, function(label, folder_id) {
                                $('#' + element_name + '-subfolder select').append(
                                    '<option value="' + folder_id + '">' + label + '</option>'
                                );
                            });
                        });
                    }
                },
                'json'
            ).done(function() {
                $('#' + element_name + '-subfolder').show();
            });
        }
    },

    changeFolderSource: function(name) {
        var element_name = 'folder_select_' + name,
            elem = $('#' + element_name + '-destination');

        $('#' + element_name + '-range-course').hide();
        $('#' + element_name + '-range-inst').hide();
        $('#' + element_name + '-subfolder').hide();
        $('#' + element_name + '-subfolder select').empty();

        if ($.inArray(elem.val(), ['courses']) > -1) {
            $('#' + element_name + '-range-course').show();
        } else if ($.inArray(elem.val(), ['institutes']) > -1) {
            $('#' + element_name + '-range-inst').show();
        } else if ($.inArray(elem.val(), ['myfiles']) > -1) {
            $('#' + element_name + '-subfolder').show();
            Files.getFolders(name);
        }
    },

    updateTermsOfUseDescription: function(e) {
        //make all descriptions invisible:
        $('div.terms_of_use_description_container > section').addClass('invisible');

        var selected_id = $(this).val();

        $('#terms_of_use_description-' + selected_id).removeClass('invisible');
    },
    openGallery: function () {
        $(".lightbox-image").first().click();
    }
};

export default Files;
