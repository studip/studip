/* ------------------------------------------------------------------------
 * Stud.IP Tour
 * ------------------------------------------------------------------------
 *
 * @author Arne Schröder, schroeder@data-quest.de
 * @description Studip Tour
 *
 * Parts of this script are a modified version of:
 *
 * jQuery aSimpleTour
 * @author alvaro.veliz@gmail.com
 * @servedby perkins (http://p.erkins.com)
 *
 * Dependencies :
 * - jQuery scrollTo
 *
 */

const Tour = {
    show_helpcenter: function() {
        jQuery('#helpbar-sticky').prop('checked', true);
    },
    hide_helpcenter: function() {
        jQuery('#helpbar-sticky').prop('checked', false);
    },
    init: function(tour_id, step_nr) {
        STUDIP.Tour.direction = 'f';
        if (!STUDIP.Tour.started && !STUDIP.Tour.pending_ajax_request) {
            STUDIP.Tour.pending_ajax_request = true;
            STUDIP.Tour.started = true;
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/tour/get_data/' + tour_id + '/' + step_nr,
                type: 'POST',
                data: { route: window.location.href },
                dataType: 'json',
                success: function(json) {
                    jQuery(document).trigger('tourstart.studip');

                    STUDIP.Tour.pending_ajax_request = false;
                    STUDIP.Tour.options = json;
                    if (STUDIP.Tour.options.redirect) {
                        window.location.href = STUDIP.Tour.options.redirect;
                    }
                    STUDIP.Tour.id = tour_id;
                    STUDIP.Tour.step = 0;
                    STUDIP.Tour.steps = STUDIP.Tour.options.data.length;
                    jQuery('body').prepend(STUDIP.Tour.options.tour_html);
                    if (!STUDIP.Tour.steps) {
                        STUDIP.Tour.started = false;
                        STUDIP.Tour.show_helpcenter();
                    } else if (STUDIP.Tour.options.last_run) {
                        STUDIP.Tour.hide_helpcenter();
                        if (STUDIP.Tour.options.tour_type === 'tour' && !STUDIP.Tour.options.edit_mode) {
                            jQuery('body').prepend('<div id="tour_overlay"></div>');
                        }
                        jQuery('#tour_title').html(STUDIP.Tour.options.last_run);
                        jQuery('#tour_end').show();
                        jQuery('#tour_next').hide();
                        jQuery('#tour_prev').hide();
                        jQuery('#tour_controls').show();
                        jQuery('#tour_reset').show();
                        jQuery('#tour_reset').on('click', function() {
                            jQuery.ajax({
                                url:
                                    STUDIP.ABSOLUTE_URI_STUDIP +
                                    'dispatch.php/tour/set_status/' +
                                    STUDIP.Tour.id +
                                    '/1/on'
                            });
                            jQuery('#tour_reset').hide();
                            jQuery('#tour_proceed').hide();
                            STUDIP.Tour.step = -1;
                            STUDIP.Tour.next();
                        });
                        jQuery('#tour_proceed').show();
                        jQuery('#tour_proceed').on('click', function() {
                            if (STUDIP.Tour.options.last_run_href) {
                                jQuery.ajax({
                                    url:
                                        STUDIP.ABSOLUTE_URI_STUDIP +
                                        'dispatch.php/tour/set_status/' +
                                        STUDIP.Tour.id +
                                        '/' +
                                        STUDIP.Tour.options.last_run_step +
                                        '/on',
                                    success: function() {
                                        window.location.href = STUDIP.URLHelper.getURL(
                                            STUDIP.Tour.options.last_run_href
                                        );
                                    }
                                });
                            }
                        });
                    } else {
                        STUDIP.Tour.hide_helpcenter();
                        if (STUDIP.Tour.options.tour_type === 'tour' && !STUDIP.Tour.options.edit_mode) {
                            jQuery('body').prepend('<div id="tour_overlay"></div>');
                        }
                        STUDIP.Tour.step = step_nr - STUDIP.Tour.options.route_step_nr - 1;
                        STUDIP.Tour.next();
                        if (STUDIP.Tour.options.edit_mode) {
                            STUDIP.Tour.startEditor();
                        }
                    }
                },
                fail: function() {
                    STUDIP.Tour.pending_ajax_request = false;
                    alert('Fehler beim Aufruf des Tour-Controllers'.toLocaleString());
                }
            });
        }
    },

    showControlButtons: function() {
        jQuery('#tour_tip').hide();
        jQuery('#tour_tip_interactive').hide();
        jQuery('.tour_focus_box').removeClass('tour_focus_box');
        jQuery('#tour_reset').hide();
        jQuery('#tour_proceed').hide();
        jQuery('#tour_end').show();
        if (STUDIP.Tour.step > 0 || STUDIP.Tour.options.back_link) {
            jQuery('#tour_prev').show();
        } else {
            jQuery('#tour_prev').hide();
        }
        if (STUDIP.Tour.step < STUDIP.Tour.steps - 1 || STUDIP.Tour.options.proceed_link) {
            jQuery('#tour_next').show();
        } else {
            jQuery('#tour_next').hide();
        }
        jQuery('#tour_controls').show();
    },

    next: function() {
        STUDIP.Tour.direction = 'f';
        STUDIP.Tour.step++;

        if (STUDIP.Tour.step >= STUDIP.Tour.steps) {
            if (STUDIP.Tour.options.proceed_link) {
                window.location.href = STUDIP.URLHelper.getURL(STUDIP.Tour.options.proceed_link);
            } else {
                this.destroy();
            }
        } else {
            if (STUDIP.Tour.options.data[STUDIP.Tour.step].action_next) {
                jQuery(STUDIP.Tour.options.data[STUDIP.Tour.step].action_next).click();
            }
            STUDIP.Tour.showControlButtons();
            STUDIP.Tour.setTooltip(STUDIP.Tour.options.data[STUDIP.Tour.step]);
        }
    },

    prev: function() {
        STUDIP.Tour.direction = 'b';
        STUDIP.Tour.step--;

        if (STUDIP.Tour.step < 0 && STUDIP.Tour.options.back_link) {
            jQuery.ajax({
                url:
                    STUDIP.ABSOLUTE_URI_STUDIP +
                    'dispatch.php/tour/set_status/' +
                    STUDIP.Tour.id +
                    '/' +
                    (STUDIP.Tour.options.route_step_nr - 1) +
                    '/on',
                success: function() {
                    window.location.href = STUDIP.URLHelper.getURL(STUDIP.Tour.options.back_link);
                }
            });
        } else {
            if (STUDIP.Tour.options.data[STUDIP.Tour.step].action_prev) {
                jQuery(STUDIP.Tour.options.data[STUDIP.Tour.step].action_prev).click();
            }
            STUDIP.Tour.showControlButtons();
            STUDIP.Tour.setTooltip(STUDIP.Tour.options.data[STUDIP.Tour.step]);
        }
    },

    setTooltip: function(stepData) {
        jQuery('.tour_focus_box').removeClass('tour_focus_box');
        var tip_id = 'tour_tip';
        if (stepData.interactive) {
            if (
                STUDIP.Tour.step === STUDIP.Tour.steps - 1 &&
                parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step !== STUDIP.Tour.options.step_count
            ) {
                jQuery('#tour_interactive_text').show();
            }
            tip_id = 'tour_tip_interactive';
        }
        jQuery('#tour_title').html(
            STUDIP.Tour.options.tour_title +
                ' (' +
                (parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step) +
                '/' +
                STUDIP.Tour.options.step_count +
                ')'
        );
        if (stepData.controlsPosition) {
            STUDIP.Tour.setControlsPosition(stepData.controlsPosition);
        }
        if (stepData.title || stepData.tip) {
            jQuery('#' + tip_id + ' #tour_tip_title').html(stepData.title);
            jQuery('#' + tip_id + ' #tour_tip_content').html(stepData.tip);

            var tooltipPos = typeof stepData.orientation === 'undefined' ? 'B' : stepData.orientation;
            STUDIP.Tour.setTooltipPosition(tooltipPos, stepData.element, tip_id);
            if (stepData.interactive && stepData.element) {
                jQuery(stepData.element).addClass('tour_focus_box');
            }
        }
    },

    setControlsPosition: function(pos) {
        var position = STUDIP.Tour.getControlPosition(pos);
        jQuery('#tour_controls').css(position);
    },

    setTooltipPosition: function(pos, element, tip_id) {
        jQuery('.tourArrow').remove();
        if (element && !jQuery(element).length) {
            //alert('Das Element wurde nicht gefunden, Tooltip konnte nicht positioniert werden.');
            element = '';
        }
        var tw =
            jQuery('#' + tip_id).width() +
            parseInt(jQuery('#' + tip_id).css('padding-left'), 10) +
            parseInt(jQuery('#' + tip_id).css('padding-right'), 10);
        var th =
            jQuery('#' + tip_id).height() +
            parseInt(jQuery('#' + tip_id).css('padding-top'), 10) +
            parseInt(jQuery('#' + tip_id).css('padding-bottom'), 10);
        if (STUDIP.Tour.options.edit_mode) {
            STUDIP.Tour.setSelectorOverlay();
            if (jQuery('#tour_edit').length) {
                jQuery('#tour_edit').attr(
                    'href',
                    STUDIP.ABSOLUTE_URI_STUDIP +
                        'dispatch.php/tour/edit_step/' +
                        STUDIP.Tour.id +
                        '/' +
                        (parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step) +
                        '?hide_route=1'
                );
                jQuery('#tour_new_step').attr(
                    'href',
                    STUDIP.ABSOLUTE_URI_STUDIP +
                        'dispatch.php/tour/edit_step/' +
                        STUDIP.Tour.id +
                        '/' +
                        (parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step + 1) +
                        '/new?hide_route=1'
                );
                jQuery('#tour_new_page').attr(
                    'href',
                    STUDIP.ABSOLUTE_URI_STUDIP +
                        'dispatch.php/tour/edit_step/' +
                        STUDIP.Tour.id +
                        '/' +
                        (parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step + 1) +
                        '/new'
                );
            }
        }
        if (!element || !pos) {
            jQuery('#' + tip_id).css({
                top: window.innerHeight / 2 - th / 2 + 'px',
                left: window.innerWidth / 2 - tw / 2 + 'px',
                position: 'fixed'
            });
            jQuery('#' + tip_id).show('fast');
            return;
        }
        var ew = jQuery(element).outerWidth();
        var eh = jQuery(element).outerHeight();
        var el = jQuery(element).offset().left;
        var et = jQuery(element).offset().top;

        var tbg = jQuery('#' + tip_id).css('background-color');
        var $upArrow = $('<div class="tourArrow"></div>').css({
            'border-left': '16px solid transparent',
            'border-right': '16px solid transparent',
            'border-bottom': '16px solid ' + tbg
        });
        var $downArrow = $('<div class="tourArrow"></div>').css({
            'border-left': '16px solid transparent',
            'border-right': '16px solid transparent',
            'border-top': '16px solid ' + tbg
        });
        var $rightArrow = $('<div class="tourArrow"></div>').css({
            'border-top': '16px solid transparent',
            'border-bottom': '16px solid transparent',
            'border-left': '16px solid ' + tbg
        });
        var $leftArrow = $('<div class="tourArrow"></div>').css({
            'border-top': '16px solid transparent',
            'border-bottom': '16px solid transparent',
            'border-right': '16px solid ' + tbg
        });
        var position;
        switch (pos) {
            case 'BL':
                position = { left: el - 10, top: et + eh + 20 };
                $upArrow.css({ top: '-16px', left: '10px' });
                jQuery('#' + tip_id).prepend($upArrow);
                break;

            case 'BR':
                position = { left: el + ew - tw + 10, top: et + eh + 20 };
                $upArrow.css({ top: '-16px', right: '10px' });
                jQuery('#' + tip_id).prepend($upArrow);
                break;

            case 'TL':
                position = { left: el - 10, top: et - th - 20 };
                $downArrow.css({ top: th, left: '10px' });
                jQuery('#' + tip_id).append($downArrow);
                break;

            case 'TR':
                position = { left: el + ew - tw + 10, top: et - th - 20 };
                $downArrow.css({ top: th, right: '10px' });
                jQuery('#' + tip_id).append($downArrow);
                break;

            case 'RT':
                position = { left: el + ew + 20, top: et - 10 };
                $leftArrow.css({ left: '-16px' });
                jQuery('#' + tip_id).prepend($leftArrow);
                break;

            case 'RB':
                position = { left: el + ew + 20, top: et + eh - th + 10 };
                $leftArrow.css({ left: '-16px' });
                jQuery('#' + tip_id).prepend($leftArrow);
                break;

            case 'LT':
                position = { left: el - tw - 20, top: et - 10 };
                $rightArrow.css({ right: '-16px' });
                jQuery('#' + tip_id).prepend($rightArrow);
                break;

            case 'LB':
                position = { left: el - tw - 20, top: et + eh - th + 10 };
                $rightArrow.css({ right: '-16px' });
                jQuery('#' + tip_id).prepend($rightArrow);
                break;

            case 'B':
                position = { left: el + ew / 2 - tw / 2, top: et + eh + 20 };
                $upArrow.css({ top: '-16px', left: tw / 2 - 16 + 'px' });
                jQuery('#' + tip_id).prepend($upArrow);
                break;

            case 'T':
                position = { left: el + ew / 2 - tw / 2, top: et - th - 20 };
                $downArrow.css({ top: th, left: tw / 2 - 16 + 'px' });
                jQuery('#' + tip_id).append($downArrow);
                break;

            case 'L':
                position = { left: el - tw - 20, top: et + eh / 2 - th / 2 };
                $rightArrow.css({ right: '-16px', top: th / 2 - 16 + 'px' });
                jQuery('#' + tip_id).prepend($rightArrow);
                break;

            case 'R':
                position = { left: el + ew + 20, top: et + eh / 2 - th / 2 };
                $leftArrow.css({ left: '-16px', top: th / 2 - 16 + 'px' });
                jQuery('#' + tip_id).prepend($leftArrow);
                break;
        }

        jQuery('#' + tip_id).css({ top: position.top + 'px', left: position.left + 'px', position: 'absolute' });
        jQuery('#' + tip_id).show('fast');
        jQuery.scrollTo(jQuery('#' + tip_id), 400, { offset: -100 });
    },

    destroy: function() {
        jQuery(document).trigger('tourend.studip');

        jQuery('#tour_overlay').remove();
        if (jQuery('#tour_selector_overlay').length) {
            jQuery('#tour_selector_overlay').hide();
        }
        if (!jQuery('#tour_proceed').is(':visible')) {
            jQuery.ajax({
                url:
                    STUDIP.ABSOLUTE_URI_STUDIP +
                    'dispatch.php/tour/set_status/' +
                    STUDIP.Tour.id +
                    '/' +
                    (parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step) +
                    '/off'
            });
        }
        jQuery('#tour_controls').hide();
        jQuery('#tour_tip').hide();
        jQuery('#tour_tip_interactive').hide();
        jQuery('.tour_focus_box').removeClass('tour_focus_box');
        STUDIP.Tour.show_helpcenter();
        STUDIP.Tour.step = -1;
        STUDIP.Tour.started = false;
    },

    setSelectorOverlay: function() {
        if (jQuery(STUDIP.Tour.options.data[STUDIP.Tour.step].element).length) {
            jQuery('#tour_selector_overlay').css({
                display: 'block',
                width: jQuery(STUDIP.Tour.options.data[STUDIP.Tour.step].element).outerWidth() + 'px',
                height: jQuery(STUDIP.Tour.options.data[STUDIP.Tour.step].element).outerHeight() + 'px',
                top: jQuery(STUDIP.Tour.options.data[STUDIP.Tour.step].element).offset().top + 'px',
                left: jQuery(STUDIP.Tour.options.data[STUDIP.Tour.step].element).offset().left + 'px'
            });
        } else {
            jQuery('#tour_selector_overlay').hide();
        }
    },

    getSelector: function(target) {
        var element = jQuery(target).prop('tagName');
        if (jQuery(target).attr('id')) {
            element = '#' + jQuery(target).attr('id');
        } else if (jQuery(target).attr('name')) {
            element = element + '[name=' + jQuery(target).attr('name') + ']';
        } else {
            if (jQuery(target).parent().length) {
                element = STUDIP.Tour.getSelector(jQuery(target).parent()) + ' ' + element;
                element = element + ':eq(' + jQuery(target).index(element) + ') ';
            }
        }
        return element;
    },

    deleteStep: function(tour_id, step_nr, button) {
        button = typeof button !== 'undefined' ? button : 'question';
        if (!STUDIP.Tour.pending_ajax_request) {
            STUDIP.Tour.pending_ajax_request = true;
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/tour/delete_step/' + tour_id + '/' + step_nr,
                data: jQuery('.modaloverlay form').serialize() + '&' + button + '=1',
                success: function(html, status, xhr) {
                    STUDIP.Tour.pending_ajax_request = false;
                    if (xhr.getResponseHeader('X-Action') === 'question') {
                        if (STUDIP.Tour.started) {
                            jQuery('#tour_controls').hide();
                            jQuery('#tour_tip').hide();
                            jQuery('#tour_tip_interactive').hide();
                            jQuery('#tour_selector_overlay').hide();
                            jQuery('.tour_focus_box').removeClass('tour_focus_box');
                        }
                        jQuery('body').prepend(html);
                        jQuery('.modaloverlay form').on('click', function(event) {
                            jQuery(this).data('clicked', jQuery(event.target));
                        });
                        jQuery('.modaloverlay form').on('submit', function(event) {
                            event.preventDefault();
                            STUDIP.Tour.deleteStep(
                                jQuery('.modaloverlay form input[name=tour_id]').val(),
                                jQuery('.modaloverlay form input[name=step_nr]').val(),
                                jQuery(this)
                                    .data('clicked')
                                    .attr('name')
                            );
                            jQuery('.modaloverlay').remove();
                        });
                    } else if (xhr.getResponseHeader('X-Action') === 'complete') {
                        if (STUDIP.Tour.started) {
                            STUDIP.Tour.showControlButtons();
                            STUDIP.Tour.setTooltip(STUDIP.Tour.options.data[STUDIP.Tour.step]);
                            STUDIP.Tour.started = false;
                            if (step_nr > 1 && step_nr - STUDIP.Tour.options.route_step_nr >= STUDIP.Tour.steps - 1) {
                                STUDIP.Tour.init(tour_id, step_nr - 1);
                            } else {
                                STUDIP.Tour.init(tour_id, step_nr);
                            }
                        }
                    }
                },
                fail: function() {
                    STUDIP.Tour.pending_ajax_request = false;
                    alert('Fehler beim Aufruf des Tour-Controllers');
                }
            });
        }
    },

    saveStep: function(tour_id, step_nr) {
        if (!STUDIP.Tour.pending_ajax_request) {
            STUDIP.Tour.pending_ajax_request = true;
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/tour/edit_step/' + tour_id + '/' + step_nr + '/save',
                type: 'POST',
                data: jQuery('#edit_tour_form').serialize(),
                dataType: 'html',
                success: function(html, status, xhr) {
                    STUDIP.Tour.pending_ajax_request = false;
                    if (xhr.getResponseHeader('X-Action') === 'close') {
                        jQuery('#edit_tour_step')
                            .parent()
                            .dialog('close');
                        if (STUDIP.Tour.started) {
                            STUDIP.Tour.started = false;
                            STUDIP.Tour.init(tour_id, step_nr);
                        } else {
                            window.location.replace(window.location.href);
                        }
                    } else {
                        jQuery('#edit_tour_step').replaceWith(html);
                    }
                },
                fail: function() {
                    STUDIP.Tour.pending_ajax_request = false;
                    alert('Fehler beim Aufruf des Tour-Controllers');
                }
            });
        }
    },

    saveStepPosition: function(tour_id, step_nr, element, mode) {
        mode = typeof mode !== 'undefined' ? mode : 'save_position';
        STUDIP.Tour.options.data[STUDIP.Tour.step].element = element;
        if (!STUDIP.Tour.pending_ajax_request) {
            STUDIP.Tour.pending_ajax_request = true;
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/tour/edit_step/' + tour_id + '/' + step_nr + '/' + mode,
                type: 'POST',
                data: { position: element },
                success: function(html, status, xhr) {
                    STUDIP.Tour.pending_ajax_request = false;
                },
                fail: function() {
                    STUDIP.Tour.pending_ajax_request = false;
                    alert('Fehler beim Aufruf des Tour-Controllers');
                }
            });
        }
    },

    startEditor: function() {
        jQuery('#tour_editor').show();
        if (STUDIP.Tour.options.step_count > 1) {
            jQuery('#tour_delete_step').show();
        } else {
            jQuery('#tour_delete_step').hide();
        }

        jQuery('#tour_delete_step').on('click', function(event) {
            STUDIP.Tour.deleteStep(STUDIP.Tour.id, parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step);
            event.preventDefault();
        });

        jQuery('#tour_no_css').on('click', function() {
            jQuery('#tour_selector_overlay').hide();
            if (jQuery('#tour_overlay').length) {
                jQuery('#tour_overlay').hide();
            }
            STUDIP.Tour.saveStepPosition(
                STUDIP.Tour.id,
                parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step,
                ''
            );
            STUDIP.Tour.setTooltip(STUDIP.Tour.options.data[STUDIP.Tour.step]);
        });

        jQuery('#tour_select_css').on('click', function() {
            jQuery('#tour_controls').hide();
            jQuery('#tour_tip').hide();
            jQuery('#tour_tip_interactive').hide();
            jQuery('#tour_selector_overlay').hide();
            if (jQuery('#tour_overlay').length) {
                jQuery('#tour_overlay').hide();
            }
            STUDIP.Tour.options.edit_mode = 'select_css';
        });

        jQuery('#tour_select_action_next').on('click', function() {
            jQuery('#tour_controls').hide();
            jQuery('#tour_tip').hide();
            jQuery('#tour_tip_interactive').hide();
            jQuery('#tour_selector_overlay').hide();
            if (jQuery('#tour_overlay').length) {
                jQuery('#tour_overlay').hide();
            }
            STUDIP.Tour.options.edit_mode = 'select_action_next';
        });

        jQuery('#tour_select_action_prev').on('click', function() {
            jQuery('#tour_controls').hide();
            jQuery('#tour_tip').hide();
            jQuery('#tour_tip_interactive').hide();
            jQuery('#tour_selector_overlay').hide();
            if (jQuery('#tour_overlay').length) {
                jQuery('#tour_overlay').hide();
            }
            STUDIP.Tour.options.edit_mode = 'select_action_prev';
        });

        if (!jQuery('#tour_selector_overlay').length) {
            jQuery('body').prepend('<div id="tour_selector_overlay" style="z-index:20000;"></div>');
        }
        jQuery('body').on('click', function(event) {
            var clicked_element;
            if (STUDIP.Tour.options.edit_mode === 'select_css') {
                clicked_element = STUDIP.Tour.getSelector(event.target);
                event.preventDefault();
                if (clicked_element !== '#tour_select_css') {
                    STUDIP.Tour.options.edit_mode = 1;
                    STUDIP.Tour.saveStepPosition(
                        STUDIP.Tour.id,
                        parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step,
                        clicked_element,
                        'save_position'
                    );
                    STUDIP.Tour.setTooltip(STUDIP.Tour.options.data[STUDIP.Tour.step]);
                    if (jQuery('#tour_overlay').length) {
                        jQuery('#tour_overlay').show();
                    }
                    STUDIP.Tour.showControlButtons();
                }
            }
            if (STUDIP.Tour.options.edit_mode === 'select_action_next') {
                clicked_element = STUDIP.Tour.getSelector(event.target);
                event.preventDefault();
                if (clicked_element !== '#tour_select_action_next') {
                    STUDIP.Tour.options.edit_mode = 1;
                    STUDIP.Tour.saveStepPosition(
                        STUDIP.Tour.id,
                        parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step,
                        clicked_element,
                        'save_action_next'
                    );
                    STUDIP.Tour.setTooltip(STUDIP.Tour.options.data[STUDIP.Tour.step]);
                    if (jQuery('#tour_overlay').length) {
                        jQuery('#tour_overlay').show();
                    }
                    STUDIP.Tour.showControlButtons();
                }
            }
            if (STUDIP.Tour.options.edit_mode === 'select_action_prev') {
                clicked_element = STUDIP.Tour.getSelector(event.target);
                event.preventDefault();
                if (clicked_element !== '#tour_select_action_prev') {
                    STUDIP.Tour.options.edit_mode = 1;
                    STUDIP.Tour.saveStepPosition(
                        STUDIP.Tour.id,
                        parseInt(STUDIP.Tour.options.route_step_nr, 10) + STUDIP.Tour.step,
                        clicked_element,
                        'save_action_prev'
                    );
                    STUDIP.Tour.setTooltip(STUDIP.Tour.options.data[STUDIP.Tour.step]);
                    if (jQuery('#tour_overlay').length) {
                        jQuery('#tour_overlay').show();
                    }
                    STUDIP.Tour.showControlButtons();
                }
            }
        });
    }
};

export default Tour;

jQuery(function() {
    //STUDIP.Tour.started = false;
    STUDIP.Tour.pending_ajax_request = false;

    jQuery(document).keyup(function(event) {
        if (STUDIP.Tour.started && event.keyCode === 37 && jQuery('#tour_prev').is(':visible')) {
            STUDIP.Tour.prev();
        } else if (STUDIP.Tour.started && event.keyCode === 39 && jQuery('#tour_next').is(':visible')) {
            STUDIP.Tour.next();
        } else if (STUDIP.Tour.started && event.keyCode === 27 && jQuery('#tour_end').is(':visible')) {
            STUDIP.Tour.destroy();
        }
    });

    jQuery(document).on('keyright', function(event) {
        STUDIP.Tour.prev();
    });
    jQuery(document).on('click', '.tour_link', function(event) {
        event.preventDefault();
        STUDIP.Tour.init(jQuery(this).attr('id'), 1);
    });

    jQuery(document).on('click', '#tour_next', function() {
        STUDIP.Tour.next();
    });

    jQuery(document).on('click', '#tour_prev', function() {
        STUDIP.Tour.prev();
    });

    jQuery(document).on('click', '#tour_end', function() {
        STUDIP.Tour.destroy();
    });
});
