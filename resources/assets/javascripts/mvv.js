import { $gettext } from './lib/gettext.js';

jQuery(function ($) {
    $(document).on('click', 'a.mvv-load-in-new-row', function () {
        STUDIP.MVV.Content.loadRow($(this));
        return false;
    });

    $(document).on('click', '.loaded-details a.cancel', function () {
        $(this).closest('.loaded-details').prev().find('toggler').click();
        return false;
    });

    STUDIP.MVV.Sort.init($('.sortable'));

    $(document).on('change', '#mvv-chooser select', function(){
        STUDIP.MVV.Chooser.create($(this));
        return false;
    });

    $(document).on('click', '.mvv-item-remove', function () {
        STUDIP.MVV.Content.removeItem(this);
        return false;
    });

    $(document).on('click', '.mvv-item-edit', function () {
        STUDIP.MVV.Content.editAnnotation(this);
        return false;
    });

    $(document).on('click', '.mvv-item-edit-properties', function () {
    	$(this).parents("li").find(".mvv-item-document-comments").toggle();
        return false;
    });

    // get the quicksearch input
    $(document).on('click focus', '.ui-autocomplete-input', function() {
        STUDIP.MVV.Search.qs_input = this;
        return false;
    });

    $('.with-datepicker').datepicker();

    $(document).on('change', '.mvv-inst-chooser select', function() {
        STUDIP.MVV.LanguageChooser.showButtons($(this));
        return false;
    });

    $(document).on('click', '.mvv-show-original', function() {
        STUDIP.MVV.Content.showOriginal($(this));
        return false;
    });

    $(document).on('click', '.mvv-show-all-original', function() {
        STUDIP.MVV.Content.showAllOriginal();
        return false;
    });

    $(document).on('click', 'a.mvv-new-tab', function(event) {
        STUDIP.MVV.Diff.openNewTab(this);
        return false;
    });

    $(document).on('click', 'input.mvv-qs-button', function($event) {
        STUDIP.MVV.Search.addSelect($(this));
        return false;
    });

    $(document).on('click', '.stgfile .remove_attachment', function($event) {
        STUDIP.MVV.Document.remove_attachment($(this));
        return false;
    });

    STUDIP.dialogReady(
        function() {

            var contactSearchParams = $('#search-contact-params');
            var contactSearchSelect = $('#search-contact-select');
            if (contactSearchParams) {
                contactSearchSelect.select2({
                    placeholder: contactSearchSelect.data('placeholder'),
                    minimumInputLength: 3,
                    ajax: {
                        url: STUDIP.URLHelper.getURL('dispatch.php/shared/contacts/search_'
                                + contactSearchSelect.data('search_type')),
                        data: function (params) {
                            var query = {
                                term: params.term,
                                _type: params._type,
                                contact_id: contactSearchParams.data('contact')
                            }
                            return query;
                        },
                        dataType: 'json'
                    }
                });
            }

            $('#search-file-select').select2({
                placeholder: $gettext('Dokument suchen'),
                minimumInputLength: 3,
                ajax: {
                    url: STUDIP.URLHelper.getURL('dispatch.php/materialien/files/search_file'),
                    dataType: 'json'
                }
            });

            $('#search-file-studiengang-select').select2({
                placeholder: $gettext('Studiengang suchen'),
                minimumInputLength: 3,
                ajax: {
                    url: STUDIP.URLHelper.getURL('dispatch.php/materialien/files/search_studiengang'),
                    dataType: 'json'
                }
            });
            $('#search-file-modul-select').select2({
                placeholder: $gettext('Modul suchen'),
                minimumInputLength: 3,
                ajax: {
                    url: STUDIP.URLHelper.getURL('dispatch.php/materialien/files/search_modul'),
                    dataType: 'json'
                }
            });
            $('#search-file-abschlusskategorie-select').select2({
                placeholder: $gettext('AbschlussKategorie suchen'),
                minimumInputLength: 3,
                ajax: {
                    url: STUDIP.URLHelper.getURL('dispatch.php/materialien/files/search_abschlusskategorie'),
                    dataType: 'json'
                }
            });
        }
    );

});

/* ------------------------------------------------------------------------
 * the local MVV namespace
 * ------------------------------------------------------------------------ */
window.STUDIP.MVV = window.STUDIP.MVV || {};

STUDIP.MVV.Search = {
    qs_input : null,
    qs_selected_name : null,
    getFocus: function (item_id, item_name) {
        var qs_input = jQuery(STUDIP.MVV.Search.qs_input),
            qs_item = jQuery('#'+qs_input.attr('id'));
        if (item_id == '') {
            STUDIP.MVV.Search.addSelect(qs_item);
        } else {
            qs_input.closest('form')
            .find('.mvv-submit')
            .show()
            .focus();
        }
        return true;
    },
    addButton: function (item_id, item_name) {
        var qs_input = jQuery(STUDIP.MVV.Search.qs_input),
            qs_item = jQuery('#'+qs_input.attr('id'));
        if (item_id == '') {
            STUDIP.MVV.Search.addSelect(qs_item);
        } else {
            STUDIP.MVV.Search.addTheButton(qs_item);
        }
        return true;
    },

    addTheButton: function (qs_item) {
        var add_button = jQuery('<a href="#" />').addClass('mvv-add-item'),
            qs_name = qs_item.attr('id'),
            target_name = qs_name.slice(0, qs_name.lastIndexOf('_')),
            item_id = jQuery('#'+qs_name+'_realvalue').val();
        jQuery('<img src="' + STUDIP.ASSETS_URL
            + 'images/icons/yellow/arr_2down.svg">')
            .attr('alt', $gettext("hinzufügen"))
            .appendTo(add_button);
        if (item_id == '') {
            qs_item.siblings('.mvv-add-button').find('.mvv-add-item')
                    .fadeOut('slow', function () {
                qs_item.val('').focus();
                jQuery(this).remove();
            });
        } else {
            add_button.click(function() {
                    if (_.isNull(STUDIP.MVV.Search.qs_selected_name)) {
                        STUDIP.MVV.Content.addItem(target_name, item_id,
                        qs_item.val());
                    } else {
                        STUDIP.MVV.Content.addItem(target_name, item_id,
                            STUDIP.MVV.Search.qs_selected_name);
                    }
                    jQuery(this).fadeOut('slow', function () {
                        qs_item.val('').focus();
                        jQuery(this).remove();
                    });
                    jQuery('#select_'+qs_name).fadeOut('fast', function(){
                        jQuery(this).next('.mvv-search-reset').fadeOut();
                        jQuery('#'+qs_name).fadeIn();
                        jQuery(this).remove();
                    });
                    return false;
                }
            );
            qs_item.siblings('.mvv-add-button').first().children('.mvv-add-item')
                .fadeOut('slow').remove();
            qs_item.siblings('.mvv-add-button').first().append(add_button);
            add_button.fadeIn('slow');
            qs_item.siblings('.mvv-select-group').fadeIn();
            add_button.focus();
            qs_item.focus(function() {
                add_button.fadeOut();
                qs_item.siblings('.mvv-select-group').fadeOut();
            });
        }
        return true;
    },

    addSelect: function (qs_item) {
        var qs_input = jQuery('#' + qs_item.data('qs_name')),
            qs_real = qs_input.prev('input'),
            qs_name = qs_input.attr('id'),
            qs_select = jQuery('<select/>').attr('id', 'select_' + qs_name)
                .addClass('mvv-search-select-list'),
            qs_id = qs_item.data('qs_id'),
            do_submit = qs_item.data('qs_submit');
        var reset_button = jQuery('<input type="image" />');
            reset_button.attr({
                src: STUDIP.ASSETS_URL+'images/icons/blue/decline.svg',
                title: $gettext("Suche zurücksetzen")
            }).addClass('mvv-search-reset');
        if (!_.isUndefined(do_submit)) {
            qs_select.change(function() {
                var selected = qs_select.children('option:selected');
                qs_real.val(selected.val());
                if (do_submit === 'yes') {
                    qs_input.closest('form').submit();
                }
            });
        } else {
            qs_select.change(function() {
                var selected = qs_select.children('option:selected');
                STUDIP.MVV.Search.addSelected.call(
                    qs_real,
                    selected.val(),
                    selected.text().trim()
                );
            });
        }
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL(STUDIP.MVV.CONTROLLER_URL + 'qs_result'),
            data: {'qs_id': qs_id, 'qs_term': qs_input.val()},
            type: 'POST',
            success: function (data) {
                for (var i in data) {
                    var d = data[i];
                    jQuery('<option/>').attr('value', d.id).text(d.name)
                        .appendTo(qs_select);
                }
                qs_input.fadeOut('fast', function () {
                    var inp = jQuery(this);
                    reset_button.click(function () {
                        qs_select.fadeOut('fast', function () {
                            reset_button.hide();
                            qs_select.remove();
                            inp.val('');
                            inp.fadeIn().focus();
                            qs_item.fadeIn();
                        });
                        reset_button.remove();
                    });
                    qs_select.insertAfter(qs_input);
                    qs_item.fadeOut('fast', function () {
                        reset_button.insertAfter(this).fadeIn();
                    });
                    qs_select.fadeIn().focus();
                });
            }
        });
    },

    submitSelected: function (item_id, item_name) {
        jQuery(this).closest('form').submit();
    },

    addSelected: function (item_id, item_name) {
        var strip_tags = /<\w+(\s+("[^"]*"|'[^']*'|[^>])+)?>|<\/\w+>/gi;
        var that = jQuery(this),
        qs_name = that.attr('name'),
            //QUICKSEARCHTODO
        //target_name = qs_id.slice(0, qs_id.lastIndexOf('_'));
        target_name = qs_name.split('_')[0];
        STUDIP.MVV.Content.addItem(target_name, item_id,
            jQuery('<div/>').html(item_name.replace(strip_tags, '')).text());
    },

   insertFachName: function (item_id, item_name) {
        $.get(STUDIP.URLHelper.getURL(STUDIP.MVV.CONTROLLER_URL + 'fach_data'), {
            fach_id: item_id
        }).done(function(d) {
            if (_.isNull(d.name)) {
                $('input[name="name"]').attr(
                    'placeholder',
                    $gettext('Keine Angabe beim Fach')
                );
            } else {
                $('input[name="name"]').attr({
                    value: d.name,
                    placeholder: d.name,
                    'aria-label': d.name,
                });
            }
            if (_.isNull(d.name_en)) {
                $('input[name="name_i18n[en_GB]"]').attr(
                    'placeholder',
                    $gettext('Keine Angabe beim Fach')
                );
            } else {
                $('input[name="name_i18n[en_GB]"]').attr('value', d.name_en);
            }
            if (_.isNull(d.name_kurz)) {
                $('input[name="name_kurz"]').attr(
                    'placeholder',
                    $gettext('Keine Angabe beim Fach')
                );
            } else {
                $('input[name="name_kurz"]').attr('value', d.name_kurz);
            }
            if (_.isNull(d.name_kurz_en)) {
                $('input[name="name_kurz_i18n[en_GB]"]').attr(
                    'placeholder',
                    $gettext('Keine Angabe beim Fach')
                );
            } else {
                $('input[name="name_kurz_i18n[en_GB]"]').attr('value', d.name_kurz_en);
            }
        });
    }
};

STUDIP.MVV.Sort = {
    i: null,
    start: function(event, ui) {
        STUDIP.MVV.Sort.i = jQuery(ui.item).index();
    },
    stop: function(event, ui) {
        var i = jQuery(ui.item).index();
        if(STUDIP.MVV.Sort.i !== i){
            var newOrder = jQuery(this).sortable('toArray');
            var tableID = jQuery(this).closest('.sortable').attr('id');
            STUDIP.MVV.Sort.save(newOrder, tableID);
        }
    },
    save: function(newOrder, tableID) {
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL(STUDIP.MVV.CONTROLLER_URL + 'sort'),
            data:{
                'list_id':tableID,
                'newOrder':newOrder
            },
            type:'POST',
            success: function() {}
        });
    },
    init: function(target) {
        target.sortable({
            items: '> .sort_items',
            cursor: 'move',
            containment: 'parent',
            axis: 'y',
            start: STUDIP.MVV.Sort.start,
            stop: STUDIP.MVV.Sort.stop
        });
    }
};

STUDIP.MVV.Chooser = {
    create: function (element) {
        var parent = element.closest('form');
        jQuery('#mvv-load-content').fadeOut().html('');
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL(parent.attr('action')),
            data: parent.serializeArray(),
            type:'POST',
            success: function(data) {
                var next = parent.nextAll();
                if (jQuery(data).is('form')) {
                    if (next.length !== 0) {
                        jQuery('.mvv-version-content').nextAll().fadeOut().remove();
                        jQuery('.mvv-version-content').fadeIn();
                        next.remove();
                    }
                    parent.after(data);
                } else {
                    location.reload();
                }
            }
        });
    }
};

STUDIP.MVV.LanguageChooser = {
    showButtons: function (element) {
        var chooser = element.closest('.mvv-inst-chooser');
        var sel = chooser.find(':selected');
        chooser.find('.mvv-inst-add-button img').fadeOut();
        if (!sel.hasClass('mvv-inst-chooser-level')) {
            var button = chooser.find('.mvv-inst-add-button img');
            button.fadeIn('fast').unbind('click');
            jQuery(button).click(function() {
                if (sel.data('fb') === '') {
                    STUDIP.MVV.Content.addItem(
                        chooser.find('select').attr('name'),
                            sel.val(), sel.text());
                } else {
                    STUDIP.MVV.Content.addItem(
                        chooser.find('select').attr('name'),
                            sel.val(),
                            sel.data('fb') + ' - ' + sel.text());
                }
            });
        }
    }
};

STUDIP.MVV.Content = {
    deskriptor_data: null,

    get: function (id) {
        jQuery('#mvv-load-content').load(
                STUDIP.URLHelper.getURL(STUDIP.MVV.CONTROLLER_URL+'content/'+id), function() {
            jQuery('#mvv-load-content').fadeIn();
        });
    },
    addItem: function (target_name, item_id, item_name) {
        var target = jQuery('#' + target_name + '_target'),
            group_id = '',
            li_id = item_id;
        if (target.hasClass('mvv-assign-group')) {
            group_id = target.siblings('.mvv-select-group').find(':selected').val();
            li_id = target_name + '_' + group_id + '_' + li_id;
        } else {
            li_id = target_name + '_' + li_id;
        }
        if (jQuery('#' + li_id).length) {
            jQuery('#' + li_id)
                .effect('highlight', {color: '#ff0000'}, 1500);
        } else {
            var item = jQuery('<li/>').attr('id', li_id);
            jQuery('<div class="mvv-item-list-text"/>')
                .text(item_name).appendTo(item);
            if (target.hasClass('sortable')) {
                item.addClass('sort_items');
            }
            target.children('.mvv-item-list-placeholder').hide();
            if (target.hasClass('mvv-assign-single')) {
                target.children().not('.mvv-item-list-placeholder').remove();
                jQuery('<input type="hidden" />')
                    .attr('name', target_name + '_item')
                    .val(item_id).appendTo(item);
            } else {
                if (target.hasClass('mvv-assign-group')) {
                    jQuery('<input type="hidden" />')
                        .attr('name', target_name+'_items_'+group_id+'[]')
                        .val(item_id).appendTo(item);
                } else {
                    jQuery('<input type="hidden" />')
                        .attr('name', target_name + '_items[]')
                        .val(item_id).appendTo(item);
                }
            }
            var button_list = jQuery('<div ' + 'class="mvv-item-list-buttons"/>')
                .append('<a href="#" class="mvv-item-remove"><img alt="Trash" src="'
                + STUDIP.ASSETS_URL
                + 'images/icons/blue/trash.svg"></a>');
            button_list.appendTo(item);
            if (target.is('.mvv-with-annotations')) {
                var text_area = jQuery('<textarea/>').attr('name',
                    target_name + '_' + 'annotations[' + item_id + ']');
                jQuery('<div/>').append(text_area).appendTo(item);
            }
            if (target.hasClass('mvv-with-properties')) {
                var prop_input = jQuery('<div/>').addClass('mvv-item-list-properties');
                jQuery('<img src="' + STUDIP.ASSETS_URL + 'images/languages/lang_de.gif"/>')
                        .appendTo(prop_input);
                jQuery('<textarea name="kommentar[' + item_id + ']"/>').appendTo(prop_input);
                jQuery('<img src="' + STUDIP.ASSETS_URL + 'images/languages/lang_en.gif"/>')
                        .appendTo(prop_input);
                jQuery('<textarea name="kommentar_en[' + item_id + ']"/>').appendTo(prop_input);
                prop_input.appendTo(item);
            }
            if (target.hasClass('mvv-assign-group')) {
                target = target.find('#'+target_name+'_'+group_id);
                target.append(item);
                target.parent().fadeIn('fast', function() {
                    item.effect('highlight', {color: '#55ff55'}, 1500);
                });
            } else {
                target.append(item);
                item.effect('highlight', {color: '#55ff55'}, 1500);
            }
        }
    },

    addItemFromDialog: function (data) {
        STUDIP.MVV.Content.addItem(data.target, data.item_id, data.item_name);
    },

    removeItem: function (this_button) {
        var item = jQuery(this_button).closest('li');
        if (item.closest('.mvv-assigned-items').hasClass('mvv-assign-group')) {
            if (item.siblings().length == 0) {
                item.parent().parent('li').fadeOut();
            }
            if (item.parent().parent().siblings(':visible').length == 0) {
                item.parent().parent()
                    .siblings('.mvv-item-list-placeholder').fadeIn('slow');
            }
        } else {
            if (item.siblings().length < 2) {
                item.siblings('.mvv-item-list-placeholder').fadeIn('slow');
            }
        }
        item.remove();
    },
    editAnnotation: function (button) {
        var this_button = jQuery(button),
            item = this_button.closest('li'),
            target_id = item.attr('id'),
            target_name = target_id.slice(0, target_id.lastIndexOf('_')),
            item_id = target_id.slice(target_id.lastIndexOf('_') + 1, target_id.length),
            annotation = item.children('.mvv-item-list-properties').first(),
            content = annotation.children('div').first();
        content.hide('slow', function () {
            jQuery('<textarea/>').attr('name', target_name + '_annotations['
                + item_id + ']').text(content.text()).hide().appendTo(annotation)
                .fadeIn();
                this_button.fadeOut();
        });
    },
    editProperties: function (button) {
        var this_button = jQuery(button),
            item = this_button.closest('li');
        STUDIP.MVV.EditForm.openRef(item);
    },
    loadRow: function (element) {
        if (element.data('busy')) {
            return false;
        }
        if (element.closest('tr').next().hasClass('loaded-details')) {
            element.closest('tbody').toggleClass('collapsed not-collapsed');
            return false;
        }
        element.data('busy', true);
        jQuery.get(element.attr('href'), '', function (response) {
            var row = jQuery('<tr />').addClass('loaded-details nohover');
            element.closest('tbody').append(row);
            element.closest('tbody').children('.loaded-details').html(response);
            element.data('busy', false);
            jQuery('body').trigger('ajaxLoaded');
            jQuery(row).show();
            STUDIP.MVV.Sort.init(jQuery('.sortable'));
            STUDIP.Table.enhanceSortableTable(row.find('.sortable-table'));
        });
        element.closest('tbody').toggleClass('collapsed not-collapsed');
        return false;
    },
    showOriginal: function (element) {
        if (element.data('hasData')) {
            element.next().slideToggle('fast');
            return false;
        };
        if (_.isNull(STUDIP.MVV.Content.deskriptor_data)) {
            jQuery.ajax({
                url: STUDIP.URLHelper.getURL(STUDIP.MVV.CONTROLLER_URL + 'show_original/'),
                data: {
                    'id'  : STUDIP.MVV.PARENT_ID,
                    'type': element.data('type')
                },
                type: 'POST',
                async: false,
                success: function (data) {
                    if (data.length !== 0) {
                        STUDIP.MVV.Content.deskriptor_data = data;
                    }
                }
            });
        }
        if (!_.isNull(STUDIP.MVV.Content.deskriptor_data)) {
            var field_id = element.closest('label')
                .find('textarea, input[type=text]')
                .attr('id');
            var item = jQuery('<div/>').addClass('mvv-orig-lang');
            if (!_.isUndefined(STUDIP.MVV.Content.deskriptor_data[field_id])) {
                if (STUDIP.MVV.Content.deskriptor_data[field_id]['empty']) {
                    item.css({
                        "color": "red",
                        "font-style": "italic"
                    });
                }
                item.html(STUDIP.MVV.Content.deskriptor_data[field_id]['value']);
            } else {
                item.html($gettext("Datenfeld in Original-Sprache nicht verfügbar."));
                item.css({
                    "color": "red",
                    "font-style": "italic"
                });
            }
            item.insertAfter(element);
            item.slideDown('fast');
            element.data('hasData', true);
        }
        return false;
    },
    showAllOriginal: function () {
        elements = jQuery('.mvv-show-original');
        _.each(elements, function (e) {
            var element = jQuery(e);
            if (element.next(':visible').length === 0) {
                element.click();
            }
        });
        return false;
    }
};

STUDIP.MVV.Diff = {
    openNewTab: function (item) {
        var url_to_open = null,
            new_id = null,
            old_id = null;
        var source = jQuery(item);
        if (source.is('a')) {
            url_to_open = item.href;
            window.open(STUDIP.URLHelper.getURL(url_to_open));
        } else {
            url_to_open = source.closest('form').attr('action');
            new_id = source.siblings('[name="new_id"]').attr('value');
            old_id = source.siblings('[name="old_id"]').attr('value');
            window.open(STUDIP.URLHelper.getURL(url_to_open,
                {'new_id': new_id, 'old_id': old_id}));
        }
        return false;
    }
};

STUDIP.MVV.Document = {
    reload_documenttable: function(range_id, range_type) {
        setTimeout(function() {
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/materialien/files/' + (typeof range_id != 'undefined' ?  'range' : 'index'),
                data: {
                    'range_id': range_id,
                    'range_type': range_type
                },
                type: 'POST',
                success: function (data) {
                    jQuery(data).each(function(){
                        jQuery('#'+ jQuery(this).attr("id")).html(jQuery(this).html());
                    });
                }
            })
        }, 100);
    },
    remove_attachment: function(item) {
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/materialien/files/delete_attachment',
            data: {
                mvvfile_id: jQuery('#mvvfile_id').val(),
                fileref_id: item.closest('li')
                                .find('input[name=document_id]')
                                .val()
            },
            type: 'POST'
        });
        item.parents('td').find('.attachments').toggle();
        item.closest('li')
            .fadeOut(300, function() {
                jQuery(this).remove();
                jQuery('#upload_chooser').show();
            });
    },
    upload_from_input: function(input, file_language) {
        STUDIP.MVV.Document.upload_files(input.files, file_language);
        jQuery(input).val('');
    },
    fileIDQueue: 1,
    upload_files: function(files, file_language) {
        for (var i = 0; i < files.length; i++) {
            var fd = new FormData();
            fd.append('file', files[i], files[i].name);
            var statusbar = jQuery('#statusbar_container .statusbar')
                .first()
                .clone()
                .show();
            statusbar.appendTo('#statusbar_container');
            fd.append('mvvfile_id', jQuery('#mvvfile_id').val());
            fd.append('range_id', jQuery('#range_id').val());
            fd.append('file_language', file_language);
            STUDIP.MVV.Document.upload_file(fd, statusbar);
        }
    },
    upload_file: function(formdata, statusbar) {
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
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/materialien/files/upload_attachment',
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            data: formdata,
            dataType: 'json'
        })
        .done(function(data) {
            statusbar.find('.progress').css({ 'min-width': '100%', 'max-width': '100%' });
            var file = jQuery('#fileselector_'+formdata.get('file_language')).find('.stgfiles > .stgfile')
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
            file.find('input[name=document_id]').attr('value', data.document_id);
            jQuery('#fileviewer_'+formdata.get('file_language')).find('.stgfiles').append(file);
            jQuery('#fileselector_'+formdata.get('file_language')).toggle();
            jQuery('#fileselector_'+formdata.get('file_language')).parents('.attachments').toggle();
            jQuery('#fileselector_'+formdata.get('file_language')).parents('.attachments').find('span').toggle();
            file.fadeIn(300);
            statusbar.find('.progresstext').text(jQuery('#upload_received_data').text());
            statusbar.delay(1000).fadeOut(300, function() {
                jQuery('#upload_chooser').hide();
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
    }
};


STUDIP.MVV.Contact = {
    reload_contacttable: function(range_id, range_type) {
        setTimeout(function() {
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/shared/contacts/' + (typeof range_id != 'undefined' ? 'range' : 'index'),
                data: {
                    'range_id': range_id,
                    'range_type': range_type
                },
                type: 'POST',
                success: function (data) {
                    jQuery(data).each(function(){
                        jQuery('#'+ jQuery(this).attr("id")).html(jQuery(this).html());
                    });
                }
            })
        }, 100);
    }
};

STUDIP.MVV.Aufbaustg = {
    create: function(df) {
        setTimeout(function() {
            $.ajax({
                url: STUDIP.URLHelper.getURL('dispatch.php/studiengaenge/studiengaenge/aufbaustg_store'),
                data: $(df).serialize(),
                type: 'POST',
                success: function (data) {
                    $('#mvv-aufbaustg-table').html($(data).html());
                    STUDIP.Table.enhanceSortableTable($('#mvv-aufbaustg-table').find('.sortable-table'));
                }
            })
        }, 100);
    },
    loadTable: function(stg_id) {
        setTimeout(function() {
            $.ajax({
                url: STUDIP.URLHelper.getURL('dispatch.php/studiengaenge/studiengaenge/aufbaustg_table/' + stg_id),
                type: 'GET',
                success: function (data) {
                    $('#mvv-aufbaustg-table').html($(data).html());
                    STUDIP.Table.enhanceSortableTable($('#mvv-aufbaustg-table').find('.sortable-table'));
                }
            })
        }, 100);
    }
}
