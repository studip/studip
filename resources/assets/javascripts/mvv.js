jQuery(function ($) {
    $(document).on('click', 'a.mvv-load-in-new-row', function () {
        MVV.Content.loadRow($(this));
        return false;
    });

    $(document).on('click', '.loaded-details a.cancel', function () {
        $(this).closest('.loaded-details').prev().find('toggler').click();
        return false;
    });

    MVV.Sort.init($('.sortable'));

    $(document).on('change', '#mvv-chooser select', function(){
        MVV.Chooser.create($(this));
        return false;
    });

    $(document).on('click', '.mvv-item-remove', function () {
        MVV.Content.removeItem(this);
        return false;
    });

    $(document).on('click', '.mvv-item-edit', function () {
        MVV.Content.editAnnotation(this);
        return false;
    });

    $(document).on('click', '.mvv-item-edit-properties', function () {
    	$(this).parents("li").find(".mvv-item-document-comments").toggle();
        return false;
    });

    // get the quicksearch input
    $(document).on('click focus', '.ui-autocomplete-input', function() {
        MVV.Search.qs_input = this;
        return false;
    });

    $('.with-datepicker').datepicker();

    $(document).on('change', '.mvv-inst-chooser select', function() {
        MVV.LanguageChooser.showButtons($(this));
        return false;
    });

    $(document).on('click', '.mvv-show-original', function() {
        MVV.Content.showOriginal($(this));
        return false;
    });

    $(document).on('click', '.mvv-show-all-original', function() {
        MVV.Content.showAllOriginal();
        return false;
    });

    $(document).on('click', 'a.mvv-new-tab', function(event) {
        MVV.Diff.openNewTab(this);
        return false;
    });

    $(document).on('click', 'input.mvv-qs-button', function($event) {
        MVV.Search.addSelect($(this));
        return false;
    });

});

/* ------------------------------------------------------------------------
 * the local MVV namespace
 * ------------------------------------------------------------------------ */
window.MVV = window.MVV || {};

MVV.Search = {
    qs_input : null,
    qs_selected_name : null,
    getFocus: function (item_id, item_name) {
        var qs_input = jQuery(MVV.Search.qs_input),
            qs_item = jQuery('#'+qs_input.attr('id'));
        if (item_id == '') {
            MVV.Search.addSelect(qs_item);
        } else {
            qs_input.closest('form')
            .find('.mvv-submit')
            .show()
            .focus();
        }
        return true;
    },
    addButton: function (item_id, item_name) {
        var qs_input = jQuery(MVV.Search.qs_input),
            qs_item = jQuery('#'+qs_input.attr('id'));
        if (item_id == '') {
            MVV.Search.addSelect(qs_item);
        } else {
            MVV.Search.addTheButton(qs_item);
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
            .attr('alt', "hinzufügen".toLocaleString())
            .appendTo(add_button);
        if (item_id == '') {
            qs_item.siblings('.mvv-add-button').find('.mvv-add-item')
                    .fadeOut('slow', function () {
                qs_item.val('').focus();
                jQuery(this).remove();
            });
        } else {
            add_button.click(function() {
                    if (_.isNull(MVV.Search.qs_selected_name)) {
                        MVV.Content.addItem(target_name, item_id,
                        qs_item.val());
                    } else {
                        MVV.Content.addItem(target_name, item_id,
                            MVV.Search.qs_selected_name);
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
                src: STUDIP.ASSETS_URL+'images/icons/blue/refresh.svg',
                title: "Suche zurücksetzen".toLocaleString()
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
                var selected = qs_select.children('option:selected'),
                target_name = qs_name.slice(0, qs_name.indexOf('_'));
                qs_real.val(selected.val());
                MVV.Content.addItem(target_name, selected.val(),
                    selected.html().replace(/<.*>/g, ' '));
                reset_button.click();
            });
        }
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL(MVV.CONTROLLER_URL + 'qs_result'),
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
        MVV.Content.addItem(target_name, item_id,
            jQuery('<div/>').html(item_name.replace(strip_tags, '')).text());
    },

   insertFachName: function (item_id, item_name) {
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL(MVV.CONTROLLER_URL + 'fach_data'),
            data: {'fach_id' : item_id},
            type: 'POST',
            success:function(d){
                if (_.isNull(d.name)) {
                    jQuery('#fach_id_1').attr('placeholder',
                        "Keine Angabe beim Fach".toLocaleString());
                } else {
                    jQuery('#fach_id_1').attr('value', d.name);
                    jQuery('#fach_id_1').attr('aria-label', d.name);
                    jQuery('#fach_id_1').attr('placeholder', d.name);
                }
                if (_.isNull(d.name_en)) {
                    jQuery('#name_en').attr('placeholder',
                        "Keine Angabe beim Fach".toLocaleString());
                } else {
                    jQuery('#name_en').attr('value', d.name_en);
                }
                if (_.isNull(d.name_kurz)) {
                    jQuery('#name_kurz').attr('placeholder',
                        "Keine Angabe beim Fach".toLocaleString());
                } else {
                    jQuery('#name_kurz').attr('value', d.name_kurz);
                }
                if (_.isNull(d.name_kurz_en)) {
                    jQuery('#name_kurz_en').attr('placeholder',
                        "Keine Angabe beim Fach".toLocaleString());
                } else {
                    jQuery('#name_kurz_en').attr('value', d.name_kurz_en);
                }
            }
        });
    }
};

MVV.Sort = {
    i: null,
    start: function(event, ui) {
        MVV.Sort.i = jQuery(ui.item).index();
    },
    stop: function(event, ui) {
        var i = jQuery(ui.item).index();
        if(MVV.Sort.i !== i){
            var newOrder = jQuery(this).sortable('toArray');
            var tableID = jQuery(this).closest('.sortable').attr('id');
            MVV.Sort.save(newOrder, tableID);
        }
    },
    save: function(newOrder, tableID) {
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL(MVV.CONTROLLER_URL + 'sort'),
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
            start: MVV.Sort.start,
            stop: MVV.Sort.stop
        });
    }
};

MVV.Chooser = {
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

MVV.LanguageChooser = {
    showButtons: function (element) {
        var chooser = element.closest('.mvv-inst-chooser');
        var sel = chooser.find(':selected');
        chooser.find('.mvv-inst-add-button img').fadeOut();
        if (!sel.hasClass('mvv-inst-chooser-level')) {
            var button = chooser.find('.mvv-inst-add-button img');
            button.fadeIn('fast').unbind('click');
            jQuery(button).click(function() {
                if (sel.data('fb') === '') {
                    MVV.Content.addItem(
                        chooser.find('select').attr('name'),
                            sel.val(), sel.text());
                } else {
                    MVV.Content.addItem(
                        chooser.find('select').attr('name'),
                            sel.val(),
                            sel.data('fb') + ' - ' + sel.text());
                }
            });
        }
    }
};

MVV.Content = {
    deskriptor_data: null,

    get: function (id) {
        jQuery('#mvv-load-content').load(
                STUDIP.URLHelper.getURL(MVV.CONTROLLER_URL+'content/'+id), function() {
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
        MVV.Content.addItem(data.target, data.item_id, data.item_name);
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
        MVV.EditForm.openRef(item);
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
            MVV.Sort.init(jQuery('.sortable'));
        });
        element.closest('tbody').toggleClass('collapsed not-collapsed');
        return false;
    },
    showOriginal: function (element) {
        if (element.data('hasData')) {
            element.next().slideToggle('fast');
            return false;
        };
        if (_.isNull(MVV.Content.deskriptor_data)) {
            jQuery.ajax({
                url: STUDIP.URLHelper.getURL(MVV.CONTROLLER_URL + 'show_original/'),
                data: {
                    'id'  : MVV.PARENT_ID,
                    'type': element.data('type')
                },
                type: 'POST',
                async: false,
                success: function (data) {
                    if (data.length !== 0) {
                        MVV.Content.deskriptor_data = data;
                    }
                }
            });
        }
        if (!_.isNull(MVV.Content.deskriptor_data)) {
            var field_id = element.closest('label')
                .find('textarea, input[type=text]')
                .attr('id');
            var item = jQuery('<div/>').addClass('mvv-orig-lang');
            if (!_.isUndefined(MVV.Content.deskriptor_data[field_id])) {
                if (MVV.Content.deskriptor_data[field_id]['empty']) {
                    item.css({
                        "color": "red",
                        "font-style": "italic"
                    });
                }
                item.html(MVV.Content.deskriptor_data[field_id]['value']);
            } else {
                item.html("Datenfeld in Original-Sprache nicht verfügbar."
                        .toLocaleString());
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

MVV.Diff = {
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
