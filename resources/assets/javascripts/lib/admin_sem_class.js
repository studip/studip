/* ------------------------------------------------------------------------
 * SemClass administration - only for root-user
 * ------------------------------------------------------------------------ */

const admin_sem_class = {
    make_sortable: function() {
        var after_update = function(event, ui) {
            if (
                jQuery(ui.item).is('.core') &&
                jQuery(this).is('#activated_plugins .droparea, #nonactivated_plugins .droparea')
            ) {
                jQuery('#deactivated_modules .droparea').append(
                    jQuery(ui.item)
                        .clone()
                        .fadeIn(1500)
                );
                jQuery(ui.item).remove();
            }
            if (jQuery(ui.item).is('.plugin:not(.core)') && jQuery(this).is('#deactivated_modules .droparea')) {
                jQuery('#nonactivated_plugins .droparea').append(
                    jQuery(ui.item)
                        .clone()
                        .fadeIn(1500)
                );
                jQuery(ui.item).remove();
            }

            jQuery('.droparea.limited').each(function(index, droparea) {
                if (jQuery(this).children().length === 0) {
                    jQuery(this).removeClass('full');
                } else {
                    jQuery(this).addClass('full');
                }
            });
            admin_sem_class.make_sortable();
        };
        jQuery('.droparea').sortable({
            connectWith: '.droparea:not(.full)',
            revert: 200,
            update: after_update
        });
        jQuery('#plugins .droparea').sortable({
            connectWith: '.droparea:not(.full, #deactivated_modules .droparea)',
            revert: 200,
            update: after_update
        });
        jQuery('#deactivated_modules .droparea').sortable({
            connectWith: '.droparea:not(.full, #plugins .droparea)',
            revert: 200,
            update: after_update
        });
    },
    saveData: function() {
        var core_module_slots = {};
        jQuery.each(
            [
                'overview',
                'forum',
                'admin',
                'documents',
                'participants',
                'schedule',
                'literature',
                'scm',
                'wiki',
                'calendar',
                'elearning_interface',
                'resources'
            ],
            function(index, element) {
                var module = jQuery('div[container=' + element + '] .droparea > div.plugin').attr('id');
                if (module) {
                    module = module.substr(module.indexOf('_') + 1);
                }
                core_module_slots[element] = module ? module : '0';
            }
        );
        var modules = {};
        jQuery('div.plugin').each(function() {
            var activated = jQuery(this)
                .find('input[name=active]')
                .is(':checked');
            var sticky =
                !jQuery(this)
                    .find('input[name=nonsticky]')
                    .is(':checked') || jQuery(this).is('#deactivated_modules div.plugin');
            var module_name = jQuery(this).attr('id');
            if (module_name) {
                module_name = module_name.substr(module_name.indexOf('_') + 1);
            }
            modules[module_name] = {
                activated: +activated,
                sticky: +sticky
            };
        });
        jQuery('#message_below').html('');
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/save',
            data: {
                sem_class_id: jQuery('#sem_class_id').val(),
                sem_class_name: jQuery('#sem_class_name').val(),
                sem_class_description: jQuery('#sem_class_description').val(),
                title_dozent: !jQuery('#title_dozent_isnull').is(':checked') ? jQuery('#title_dozent').val() : '',
                title_dozent_plural: !jQuery('#title_dozent_isnull').is(':checked')
                    ? jQuery('#title_dozent_plural').val()
                    : '',
                title_tutor: !jQuery('#title_tutor_isnull').is(':checked') ? jQuery('#title_tutor').val() : '',
                title_tutor_plural: !jQuery('#title_tutor_isnull').is(':checked')
                    ? jQuery('#title_tutor_plural').val()
                    : '',
                title_autor: !jQuery('#title_autor_isnull').is(':checked') ? jQuery('#title_autor').val() : '',
                title_autor_plural: !jQuery('#title_autor_isnull').is(':checked')
                    ? jQuery('#title_autor_plural').val()
                    : '',
                core_module_slots: core_module_slots,
                modules: modules,
                workgroup_mode: jQuery('#workgroup_mode').is(':checked') ? 1 : 0,
                studygroup_mode: jQuery('#studygroup_mode').is(':checked') ? 1 : 0,
                only_inst_user: jQuery('#only_inst_user').is(':checked') ? 1 : 0,
                default_read_level: jQuery('#default_read_level').val(),
                default_write_level: jQuery('#default_write_level').val(),
                bereiche: jQuery('#bereiche').is(':checked') ? 1 : 0,
                module: jQuery('#module').is(':checked') ? 1 : 0,
                show_browse: jQuery('#show_browse').is(':checked') ? 1 : 0,
                write_access_nobody: jQuery('#write_access_nobody').is(':checked') ? 1 : 0,
                topic_create_autor: jQuery('#topic_create_autor').is(':checked') ? 1 : 0,
                visible: jQuery('#visible').is(':checked') ? 1 : 0,
                course_creation_forbidden: jQuery('#course_creation_forbidden').is(':checked') ? 1 : 0,
                create_description: jQuery('#create_description').val(),
                admission_prelim_default: jQuery('#admission_prelim_default').val(),
                admission_type_default: jQuery('#admission_type_default').val(),
                show_raumzeit: jQuery('#show_raumzeit').is(':checked') ? 1 : 0,
                is_group: jQuery('#is_group').is(':checked') ? 1 : 0
            },
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                jQuery('#message_below').html(data.html);
            }
        });
    },
    delete_sem_type_question: function() {
        var sem_type = jQuery(this)
            .closest('li')
            .attr('id');
        sem_type = sem_type.substr(sem_type.lastIndexOf('_') + 1);
        jQuery('#sem_type_for_deletion').val(sem_type);
        jQuery('#sem_type_delete_question').dialog({
            title: jQuery('#sem_type_delete_question_title').text()
        });
    },
    add_sem_type: function() {
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/add_sem_type',
            type: 'post',
            data: {
                sem_class: jQuery('#sem_class_id').val(),
                name: jQuery('#new_sem_type').val()
            },
            success: function(ret) {
                jQuery('#sem_type_list').append(jQuery(ret));
                jQuery('#new_sem_type')
                    .val('')
                    .closest('li')
                    .children()
                    .toggle();
            },
            error: function() {
                jQuery('#new_sem_type')
                    .val('')
                    .closest('li')
                    .children()
                    .toggle();
            }
        });
    },
    delete_sem_type: function() {
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/delete_sem_type',
            data: {
                sem_type: jQuery('#sem_type_for_deletion').val()
            },
            type: 'post',
            success: function() {
                jQuery('#sem_type_' + jQuery('#sem_type_for_deletion').val()).remove();
                jQuery('#sem_type_delete_question').dialog('close');
            }
        });
    },
    rename_sem_type: function() {
        jQuery(this)
            .closest('span.name_container')
            .children()
            .toggle();
        var name = this.value;
        var old_name = jQuery(this)
            .closest('.name_container')
            .find('.name_html');
        var sem_type = jQuery(this)
            .closest('li')
            .attr('id');
        sem_type = sem_type.substr(sem_type.lastIndexOf('_') + 1);
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/rename_sem_type',
            data: {
                sem_type: sem_type,
                name: name
            },
            type: 'post',
            success: function() {
                old_name.text(name);
            }
        });
    }
};

export default admin_sem_class;
