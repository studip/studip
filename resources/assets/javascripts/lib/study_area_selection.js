/* ------------------------------------------------------------------------
 * study area selection for courses
 * ------------------------------------------------------------------------ */
const study_area_selection = {
    initialize: function() {
        // Ein bisschen hässlich im Sinne von "DRY", aber wie sonst?
        jQuery(document).on('click', 'input[name^="study_area_selection[add]"]', function() {
            var parameters = jQuery(this).data();
            if (!(parameters && parameters.id)) {
                return;
            }
            study_area_selection.add(parameters.id, parameters.course_id || '-');
            return false;
        });
        jQuery(document).on('click', 'input[name^="study_area_selection[remove]"]', function() {
            var parameters = jQuery(this).data();
            if (!(parameters && parameters.id)) {
                return;
            }
            study_area_selection.remove(parameters.id, parameters.course_id || '-');
            return false;
        });
        jQuery(document).on('click', 'a.study_area_selection_expand', function() {
            var parameters = jQuery(this).data();
            if (!(parameters && parameters.id)) {
                return;
            }
            study_area_selection.expandSelection(parameters.id, parameters.course_id || '-');
            return false;
        });
    },

    url: function(/* action, args...*/) {
        return STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/course/study_areas/' + jQuery.makeArray(arguments).join('/');
    },

    add: function(id, course_id) {
        // may not be visible at the current
        jQuery('.study_area_selection_add_' + id)
            .prop('disabled', true)
            .fadeTo('slow', 0);

        jQuery.ajax({
            type: 'POST',
            url: study_area_selection.url('add', course_id || '-'),
            data: { id: id },
            dataType: 'html',
            async: false, // Critical request thus synchronous
            success: function(data) {
                jQuery('#study_area_selection_none').fadeOut();
                jQuery('#study_area_selection_selected').replaceWith(data);
                study_area_selection.refreshSelection();
            }
        });
    },

    remove: function(id, course_id) {
        var jQueryselection = jQuery('#study_area_selection_' + id);

        if (jQueryselection.siblings().length === 0) {
            jQuery('#study_area_selection_at_least_one')
                .fadeIn()
                .delay(5000)
                .fadeOut();
            jQueryselection.effect('bounce', 'fast');
            return;
        }

        jQuery.ajax({
            type: 'POST',
            url: study_area_selection.url('remove', course_id || '-'),
            data: { id: id },
            dataType: 'html',
            async: false, // Critical request thus synchronous
            success: function() {
                jQueryselection.fadeOut(function() {
                    jQuery(this).remove();
                });
                if (jQuery('#study_area_selection_selected li').length === 0) {
                    jQuery('#study_area_selection_none').fadeIn();
                }
                jQuery('.study_area_selection_add_' + id)
                    .css({
                        visibility: 'visible',
                        opacity: 0
                    })
                    .fadeTo('slow', 1, function() {
                        jQuery(this).prop('disabled', false);
                    });

                study_area_selection.refreshSelection();
            },
            error: function() {
                jQueryselection.fadeIn();
            }
        });
    },

    expandSelection: function(id, course_id) {
        jQuery.post(
            study_area_selection.url('expand', course_id || '-', id),
            function(data) {
                jQuery('#study_area_selection_selectables ul').replaceWith(data);
            },
            'html'
        );
    },

    refreshSelection: function() {
        // "even=odd && odd=even ??" - this may seem strange but jQuery and Stud.IP differ in odd/even
        jQuery('#study_area_selection_selected li:odd')
            .removeClass('odd')
            .addClass('even');
        jQuery('#study_area_selection_selected li:even')
            .removeClass('even')
            .addClass('odd');
    }
};

export default study_area_selection;
