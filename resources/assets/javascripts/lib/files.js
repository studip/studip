/*jslint esversion: 6*/
import { $gettext } from './gettext.js';
import Dialog from './dialog.js';
import FilesTable from '../../../vue/components/FilesTable.vue';

const Files = {
    init () {
        if ($('#files-index, #files-system, #course-files-index, #institute-files-index, #files-flat, #course-files-flat, #institute-files-flat, #files-overview').length
            && jQuery("#files_table_form").length) {

            STUDIP.Vue.load().then(({createApp}) => {
                this.filesapp = createApp({
                    el: "#layout_content",
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
                        },
                        removeFile(id) {
                            this.files = this.files.filter(file => file.id != id)
                        }
                    },
                    components: { FilesTable, },
                });
            });
        }

        //The following is only for (read only) vue file tables where multiple
        //tables are displayed in one page.
        var tables = jQuery('.vue-file-table');
        if (tables.length) {
            for (var table of tables) {
                STUDIP.Vue.load().then(({createApp}) => {
                    createApp({
                        el: table,
                        data: {
                            "files":       jQuery(table).data("files") || [],
                            "folders":     jQuery(table).data("folders") || [],
                            "topfolder":   jQuery(table).data("topfolder"),
                            "breadcrumbs": jQuery(table).data("breadcrumbs") || []
                        },
                        components: { FilesTable, },
                    });
                });
            }
        }
    },

    openAddFilesWindow: function(folder_id) {
        var responsive_mode = jQuery('html').first().hasClass('responsive-display');
        if ($('.files_source_selector').length > 0) {
            Dialog.show($('.files_source_selector').html(), {
                title: $gettext('Dokument hinzufügen'),
                size: (responsive_mode ? undefined : 'auto')
            });
        } else {
            Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/add_files_window/' + folder_id), {
                title: $gettext('Dokument hinzufügen'),
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
        var files = 0;
        var folder_id = $('.files_source_selector').data('folder_id');
        var thresholds = []
        var data = new FormData();
        var updater_enabled = STUDIP.jsupdate_enable;

        //Open upload-dialog
        const nameslist = $('.file_upload_window .filenames').show().empty();
        $('.file_upload_window .errorbox').hide().find('.errormessage').empty();

        var total_size = 0;
        $.each(filelist, function(index, file) {
            if (Files.validateUpload(file)) {
                data.append('file[]', file, file.name);

                var id = `upload-element-${index}`;
                var li = $('<li/>').attr('id', id).appendTo(nameslist);
                $('<span/>').text(file.name).appendTo(li);
                $('<span class="upload-progress"/>').appendTo(li);

                thresholds.push({
                    position: total_size,
                    threshold: total_size + file.size,
                    name: file.name,
                    size: file.size,
                    element: id
                });

                total_size += file.size;
                files += 1;
            } else {
                const errorMessage = file.name + ': ' + $gettext('Datei ist zu groß oder hat eine nicht erlaubte Endung.') + "<br>";
                $('.file_upload_window .errorbox').show().find('.errormessage').append(errorMessage);
            }
        });
        if ($('.file_uploader').length > 0) {
            Dialog.show($('.file_uploader').html(), {
                title: $gettext('Datei hochladen')
            });
        } else {
            Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/upload_window'), {
                title: $gettext('Datei hochladen')
            });
        }

        //start upload
        $('form.drag-and-drop.files').removeClass('hovered');
        if (files > 0) {
            STUDIP.JSUpdater.stop();

            $('.file_upload_window .uploadbar').show().filter('.uploadbar-inner').css({
                right: '100%'
            });
            $.ajax({
                url: STUDIP.URLHelper.getURL(`dispatch.php/file/upload/${folder_id}`),
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                xhr: () => {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        const uploadbar      = $('.file_upload_window .uploadbar-inner');
                        const uploadprogress = $('.file_upload_window .uploadbar .upload-progress');
                        var last = null;
                        xhr.upload.addEventListener('progress', event => {
                            if (event.lengthComputable) {
                                //Set progress
                                const position = event.loaded || event.position;
                                const total = event.total;
                                const percent = Math.round(position / total * 100 * 100) / 100;

                                uploadbar.css('right', `${100 - percent}%`);
                                uploadprogress.text(`${percent}%`);

                                const current = thresholds.find(element => element.threshold >= position);
                                if (current) {
                                    const current_percent = Math.round((position - current.position) / current.size * 100);
                                    $(`#${current.element} .upload-progress`).text(`${current_percent}%`);

                                    if (current.element !== last && last !== null) {
                                        $(`#${last} .upload-progress`).text(`100%`).closest('li').prevAll('li').find('.upload-progress').text('100%');
                                    }
                                    last = current.element;
                                }
                            }
                        }, false);
                    }

                    $(document).on('dialog-close.xhr-upload', () => xhr.abort());

                    return xhr;
                }
            }).done(json => {
                $('.file_upload_window .uploadbar-inner').css('right', '0');
                $('.file_upload_window .upload-progress').text(`100%`);

                $(document).off('.xhr-upload');
            }).fail((jqxhr, textStatus, error) => {
                const errorMessage = $gettext('Es gab einen Fehler beim Hochladen der Datei(en):') + ' ' + error;
                $('.file_upload_window .errorbox').show().find('.errormessage').text(errorMessage);
                $('.file_upload_window').children('.filenames,.uploadbar').hide();
            }).always(() => {
                if (updater_enabled) {
                    STUDIP.JSUpdater.start();
                }
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
            html = html === null ? [] : [html];
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
            html = html === null ? [] : [html];
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
            ).done(() => {
                $(`#${element_name}-subfolder`).show();
            });
        }
    },

    changeFolderSource: function(name) {
        var element_name = `folder_select_${name}`;
        var elem = $(`#${element_name}-destination`);

        $(`#${element_name}-range-course`).toggle(elem.val() === 'courses');
        $(`#${element_name}-range-inst`).toggle(elem.val() === 'institutes');
        $(`#${element_name}-subfolder`).toggle(elem.val() === 'myfiles');

        if (elem.val() === 'myfiles') {
            $(`#${element_name}-subfolder select`).empty();
            Files.getFolders(name);
        }
    },

    updateTermsOfUseDescription: function(e) {
        //make all descriptions invisible:
        $('div.terms_of_use_description_container > section').addClass('invisible');

        var selected_id = $(this).val();

        $(`#terms_of_use_description-${selected_id}`).removeClass('invisible');
    },

    openGallery: function () {
        $(".lightbox-image").first().click();
    },

    // Upload constraints
    uploadConstraints: false,

    setUploadConstraints (constraints) {
        Files.uploadConstraints = constraints;
    }
};

export default Files;