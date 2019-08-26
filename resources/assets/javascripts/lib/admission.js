/* ------------------------------------------------------------------------
 * Anmeldeverfahren und -sets
 * ------------------------------------------------------------------------ */
import Dialog from './dialog.js';
import Dialogs from './dialogs.js';

const Admission = {
    getCourses: function(targetUrl) {
        var courseFilter = $('input[name="course_filter"]').val();
        if (courseFilter == '') {
            courseFilter = '%%%';
        }
        var data = {
            'courses[]': _.map($('#courselist input:checked'), 'id'),
            course_filter: courseFilter,
            semester: $('select[name="semester"]').val(),
            'institutes[]': $.merge(
                _.map($('input[name="institutes[]"]:hidden'), 'value'),
                _.map($('input[name="institutes[]"]:checked'), 'value')
            )
        };
        var loading = 'Wird geladen'.toLocaleString();
        $('#instcourses').empty();
        $('<img/>', {
            src: STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'
        }).appendTo('#instcourses');
        $('#instcourses').append(loading);
        $('#instcourses').load(targetUrl, data);
        return false;
    },

    configureRule: function(ruleType, targetUrl, ruleId) {
        var urlparts = targetUrl.split('?');
        targetUrl = urlparts[0] + '/' + ruleType;
        if (urlparts[1]) {
            targetUrl += '?' + urlparts[1];
        }

        Dialog.fromURL(targetUrl, {
            method: 'post',
            width: 'auto',
            title: 'Anmelderegel konfigurieren'.toLocaleString(),
            id: 'configurerule',
            data: { ruleId: ruleId, rules: _.map($('#rules input[name="rules[]"]'), 'value') }
        });

        return false;
    },

    selectRuleType: function(source) {
        Dialog.fromURL(source, {
            title: 'Anmelderegel konfigurieren'.toLocaleString(),
            size: 'auto',
            data: { rules: _.map($('#rules input[name="rules[]"]'), 'value') },
            method: 'post',
            id: 'configurerule'
        });
        return false;
    },

    saveRule: function(ruleId, targetId, targetUrl) {
        if ($('#action').val() !== 'cancel') {
            $.ajax({
                type: 'post',
                url: targetUrl,
                data: $('#ruleform').serialize(),
                dataType: 'html',
                success: function(data, textStatus, jqXHR) {
                    if (data !== '') {
                        var result = '';
                        if ($('#norules').length > 0) {
                            $('#norules').remove();
                            $('#' + targetId).prepend('<div id="rulelist"></div>');
                        }
                        result += data;
                        if ($('#rule_' + ruleId).length !== 0) {
                            $('#rule_' + ruleId).replaceWith(result);
                        } else {
                            $('#rulelist').append(result);
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Status: ' + textStatus + '\nError: ' + errorThrown);
                }
            });
        }
        Admission.closeDialog('configurerule');
        Admission.toggleNotSavedAlert();
        return false;
    },

    removeRule: function(targetId, containerId) {
        var parent = $('#' + targetId).parent();
        $('#' + targetId).remove();
        if (parent.children('div').length === 0) {
            parent.remove();
            var norules = 'Sie haben noch keine Anmelderegeln festgelegt.'.toLocaleString();
            $('#' + containerId).prepend('<span id="norules">' + '<i>' + norules + '</i></span>');
        }
        Dialogs.closeConfirmDialog();
        Admission.toggleNotSavedAlert();
    },

    toggleRuleDescription: function(targetId) {
        $('#' + targetId).toggle();
        return false;
    },

    toggleDetails: function(arrowId, detailId) {
        var oldSrc = $('#' + arrowId).attr('src');
        var newSrc = $('#' + arrowId).attr('rel');
        $('#' + arrowId).attr('src', newSrc);
        $('#' + arrowId).attr('rel', oldSrc);
        $('#' + detailId).slideToggle();
        return false;
    },

    /**
     *
     * @param String ruleId      The rule to save.
     * @param String errorTarget Target element ID where error messages will be
     *                           shown.
     * @param String validateUrl URL to call for validation.
     * @param String savedTarget Target element ID where the saved rule will be
     *                           displayed.
     * @param String saveUrl     URL to save the rule.
     */
    checkAndSaveRule: function(ruleId, errorTarget, validateUrl, savedTarget, saveUrl) {
        if (Admission.validateRuleConfig(errorTarget, validateUrl)) {
            Admission.saveRule(ruleId, savedTarget, saveUrl);
            Dialog.close({ id: 'configurerule' });
        }
        return false;
    },

    validateRuleConfig: function(containerId, targetUrl) {
        var valid = true;
        var error = $.ajax({
            type: 'post',
            async: false,
            url: targetUrl,
            data: $('#ruleform').serialize(),
            dataType: 'html',

            error: function(jqXHR, textStatus, errorThrown) {
                alert('Status: ' + textStatus + '\nError: ' + errorThrown);
            }
        }).responseText;
        error = error.replace(/(\r\n|\n|\r)/gm, '');
        if ($.trim(error) != '') {
            $('#' + containerId).html(error);
            valid = false;
        }
        return valid;
    },

    removeUserFromUserlist: function(userId) {
        var parent = $('#user_' + userId).parent();
        $('#user_' + userId).remove();
        if (parent.children('li').length === 0) {
            var nousers = 'Sie haben noch niemanden hinzugefügt.'.toLocaleString();
            $(parent)
                .parent()
                .append('<span id="nousers">' + '<i>' + nousers + '</i></span>');
        }
        return false;
    },

    /**
     * Creates a tree view from the HTML list in <elementId> using the
     * given data for special node types.
     *
     * @param String elementId
     * @param typesData JS object with tree nodes types
     *          (@see http://www.jstree.com/documentation/types)
     */
    makeTree: function(elementId, typesData) {
        var config = {
            core: {
                animation: 100,
                open_parents: true,
                initially_open: ['root']
            },
            checkbox: {
                real_checkboxes: true,
                selected_parent_open: true,
                override_ui: false,
                two_state: true
            },
            plugins: ['html_data', 'themes', 'types', 'checkbox', 'ui']
        };
        config.types = { types: typesData };
        $('#' + elementId)
            .on('loaded.jstree', function(event, data) {
                // Show checked checkboxes.
                var checkedItems = $('#' + elementId).find('.jstree-checked');
                checkedItems.removeClass('jstree-unchecked');
                // Open parent nodes of checked nodes.
                checkedItems.parents().each(function() {
                    data.inst.open_node(this, false, true);
                });
            })
            .jstree(config);
    },

    updateInstitutes: function(elementId, instURL, courseURL, mode) {
        if (elementId !== '') {
            var query = '';
            $('.institute').each(function() {
                query += '&institutes[]=' + this.value;
            });
            switch (mode) {
                case 'delete':
                    $('#' + elementId).remove();
                    break;
                case 'add':
                    query += '&institutes[]=' + elementId;
                    $.post(instURL, query, function(data) {
                        $('#institutes').html(data);
                    });
                    break;
            }
            $('#instcourses :checked').each(function() {
                query += '&courses[]=' + this.value;
            });
            this.getCourses(courseURL);
            Admission.toggleNotSavedAlert();
        }
    },

    checkRuleActivation: function(target) {
        var form = $('#' + target);
        var globalActivation = form.find('input[name=enabled]');
        if (globalActivation.prop('checked')) {
            $('#activation').show();
            if (form.find('input[name=activated]:checked').val() === 'studip') {
                $('#institutes_activation').hide();
            } else {
                $('#institutes_activation').show();
            }
        } else {
            $('#activation').hide();
            $('#institutes_activation').hide();
        }
    },

    closeDialog: function(elementId) {
        $('#' + elementId).remove();
    },

    checkUncheckAll: function(inputName, mode) {
        switch (mode) {
            case 'check':
                $('input[name*="' + inputName + '"]').each(function() {
                    $(this).prop('checked', true);
                });
                break;
            case 'uncheck':
                $('input[name*="' + inputName + '"]').each(function() {
                    $(this).prop('checked', false);
                });
                break;
            case 'invert':
                $('input[name*="' + inputName + '"]').each(function() {
                    $(this).prop('checked', !$(this).prop('checked'));
                });
                break;
        }
        return false;
    },

    toggleNotSavedAlert: function() {
        $('.hidden-alert').show();
    }
};

export default Admission;
