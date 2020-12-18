/* ------------------------------------------------------------------------
 * Bedingungen zur Auswahl von Stud.IP-Nutzern
 * ------------------------------------------------------------------------ */
import { $gettext } from './gettext.js';
import Dialog from './dialog.js';

const UserFilter = {
    new_group_nr: 1,

    configureCondition: function(targetId, targetUrl) {
        Dialog.fromURL(targetUrl, {
            title: $gettext('Bedingung konfigurieren'),
            size: Math.min(Math.round(0.9 * $(window).width()), 850) + 'x400',
            method: 'post',
            id: 'configurecondition'
        });
        return false;
    },

    /**
     * Adds a new user filter to the list of set filters.
     * @param String containerId
     * @param String targetUrl
     */
    addCondition: function(containerId, targetUrl) {
        var children = $('.conditionfield');
        var query = '';
        $('.conditionfield').each(function() {
            query +=
                '&field[]=' +
                encodeURIComponent(
                    $(this)
                        .children('.conditionfield_class:first')
                        .val()
                ) +
                '&compare_operator[]=' +
                encodeURIComponent(
                    $(this)
                        .children('.conditionfield_compare_op:first')
                        .val()
                ) +
                '&value[]=' +
                encodeURIComponent(
                    $(this)
                        .children('.conditionfield_value:first')
                        .val()
                );
        });
        $.ajax({
            type: 'post',
            url: targetUrl,
            data: query,
            dataType: 'html',
            success: function(data, textStatus, jqXHR) {
                var result = '';
                if ($('#' + containerId).children('.nofilter:visible').length > 0) {
                    $('#' + containerId)
                        .children('.nofilter')
                        .hide();
                    $('#' + containerId)
                        .children('.userfilter')
                        .show();
                } else if ($('#' + containerId).children('.ungrouped_conditions .condition_list').length > 0) {
                    result += '<b>' + $gettext('oder') + '</b>';
                }
                result += data;
                $('#' + containerId)
                    .find('.userfilter .ungrouped_conditions .condition_list')
                    .append(result);
                if ($('#no_conditiongroups').length > 0) {
                    $('.userfilter .ungrouped_conditions .condition_list input[type=checkbox]').hide();
                }
                $('.userfilter .group_conditions').show();
            }
        });
        Dialog.close({ id: 'configurecondition' });
    },

    /**
     * groups selected conditions
     */
    groupConditions: function() {
        var selected = $('.userfilter input:checked').parent('div');
        var group_template = $('.grouped_conditions_template').clone();
        if (selected.length > 0) {
            $('.userfilter input[type=checkbox]:checked')
                .prop('checked', false)
                .hide();
            $('.userfilter .group_conditions').after(group_template.show());
            selected.find('input[name^=conditiongroup_]').prop('value', UserFilter.new_group_nr);
            $('.grouped_conditions_template:last .condition_list').append(selected);
            $('.grouped_conditions_template:last .condition_list input[name=quota]').prop(
                'name',
                'quota_' + UserFilter.new_group_nr
            );
            $('.grouped_conditions_template:last').prop('id', 'new_conditiongroup_' + UserFilter.new_group_nr);
            $('.grouped_conditions_template:last').prop('class', 'grouped_conditions');
            UserFilter.new_group_nr++;
        }
        if ($('.userfilter .ungrouped_conditions .condition_list .condition').length == 0) {
            $('.userfilter .group_conditions').hide();
        }
        return false;
    },

    /**
     * removes group for conditions
     */
    ungroupConditions: function(element) {
        var selected = $(element)
            .parents('.grouped_conditions')
            .find('.condition');
        var empty_group = $(element).parents('.grouped_conditions');
        if (selected.length > 0) {
            selected.find('input[name^=conditiongroup_]').prop('value', '');
            $('.ungrouped_conditions .condition_list').append(selected);
            $('.ungrouped_conditions input[type=checkbox]:not(:visible)').show();
            empty_group.remove();
        }
        $('.userfilter .group_conditions').show();
        return false;
    },

    getConditionFieldConfiguration: function(element, targetUrl) {
        var target = $(element).parent();
        $.ajax(targetUrl, {
            url: targetUrl,
            data: { fieldtype: $(element).val() },
            success: function(data, textStatus, jqXHR) {
                target.children('.conditionfield_compare_op').remove();
                target.children('.conditionfield_value').remove();
                target
                    .children('.conditionfield_delete')
                    .first()
                    .before(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Status: ' + textStatus + '\nError: ' + errorThrown);
            }
        });
        return false;
    },

    addConditionField: function(targetId, targetUrl) {
        $.ajax({
            url: targetUrl,
            success: function(data, textStatus, jqXHR) {
                $('#' + targetId).append(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Status: ' + textStatus + '\nError: ' + errorThrown);
            }
        });
        return false;
    },

    removeConditionField: function(element) {
        element.remove();
        Dialogs.closeConfirmDialog();
        return false;
    },

    closeDialog: function(button) {
        var dialog = $(button)
            .parents('div[role=dialog]')
            .first();
        dialog.remove();
        return false;
    }
};

export default UserFilter;
