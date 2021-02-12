import { $gettext } from '../lib/gettext.js';

const Questionnaire = {
    lastUpdate: null,
    initialize() {
        STUDIP.JSUpdater.register(
            'questionnaire',
            Questionnaire.updateQuestionnaireResults,
            Questionnaire.getParamsForPolling,
            15000
        );
    },
    getParamsForPolling: function() {
        var questionnaires = {
            questionnaire_ids: [],
            last_update: Questionnaire.lastUpdate
        };
        Questionnaire.lastUpdate = Math.floor(Date.now() / 1000);
        jQuery('.questionnaire_results').each(function() {
            questionnaires.questionnaire_ids.push(jQuery(this).data('questionnaire_id'));
        });
        if (questionnaires.questionnaire_ids.length > 0) {
            return questionnaires;
        }
    },
    updateQuestionnaireResults: function(data) {
        for (var questionnaire_id in data) {
            if (data[questionnaire_id].html) {
                var new_view = jQuery(data[questionnaire_id].html);
                jQuery('.questionnaire_results.questionnaire_' + questionnaire_id).replaceWith(new_view);
                jQuery(document).trigger('dialog-open');
            }
        }
    },
    updateOverviewQuestionnaire: function(data) {
        if (jQuery('#questionnaire_overview tr#questionnaire_' + data.questionnaire_id).length > 0) {
            jQuery('#questionnaire_overview tr#questionnaire_' + data.questionnaire_id).replaceWith(data.overview_html);
        } else {
            if (jQuery('#questionnaire_overview').length > 0) {
                jQuery(data.overview_html)
                    .hide()
                    .insertBefore('#questionnaire_overview > tbody > :first-child')
                    .delay(300)
                    .fadeIn();
                jQuery('#questionnaire_overview .noquestionnaires').remove();
            }
            if (data.message) {
                jQuery('.messagebox').hide();
                jQuery('#layout_content').prepend(data.message);
            }
        }
        if (jQuery('.questionnaire_widget .widget_questionnaire_' + data.questionnaire_id).length > 0) {
            if (data.widget_html) {
                jQuery('.questionnaire_widget .widget_questionnaire_' + data.questionnaire_id).replaceWith(
                    data.widget_html
                );
            } else {
                jQuery('.questionnaire_widget .widget_questionnaire_' + data.questionnaire_id).remove();
            }
        } else {
            if (jQuery('.questionnaire_widget').length > 0 && data.widget_html) {
                jQuery('.ui-dialog-content').dialog('close');
                if (jQuery('.questionnaire_widget > article').length > 0) {
                    jQuery(data.widget_html)
                        .hide()
                        .insertBefore(
                            '.questionnaire_widget > article:first-of-type, .questionnaire_widget > section:first-of-type'
                        )
                        .delay(300)
                        .fadeIn();
                } else {
                    jQuery('.questionnaire_widget .noquestionnaires')
                        .replaceWith(data.widget_html)
                        .hide()
                        .delay(300)
                        .fadeIn();
                }
            } else {
                if (data.message) {
                    jQuery('.messagebox').hide();
                    jQuery('#layout_content').prepend(data.message);
                    jQuery.scrollTo('#layout_content', 400);
                }
            }
        }
        jQuery(document).trigger('dialog-open');
    },
    updateWidgetQuestionnaire: function(html) {
        //update the results of a questionnaire
        var questionnaire_id = jQuery(html).data('questionnaire_id');
        jQuery('.questionnaire_widget .questionnaire_' + questionnaire_id).replaceWith(html);
        jQuery(document).trigger('dialog-open');
    },
    beforeAnswer: function() {
        var form = jQuery(this).closest('form')[0];
        var questionnaire_id = jQuery(form)
            .closest('article')
            .data('questionnaire_id');
        let validated = true;

        //validation
        $(form).find("input, select, textarea").each(function () {
            if ($(this).is(":invalid")) {
                validated = false;
            }
        });

        $(form).find(".questionnaire_answer > article").each(function () {
            let question_type = $(this).data("question_type");
            if (typeof STUDIP.Questionnaire[question_type] !== "undefined"
                    && typeof STUDIP.Questionnaire[question_type].validator === "function") {
                if (!STUDIP.Questionnaire[question_type].validator.call(this)) {
                    validated = false;
                }
            }
        });

        if (!validated) {
            $(form).addClass("show_validation_hints");
            STUDIP.Report.warning($gettext("Noch nicht komplett ausgefüllt."), $gettext("Füllen Sie noch die rot markierten Stellen korrekt aus."));
            return false;
        }

        if (jQuery(form).is('.questionnaire_widget form')) {
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/questionnaire/submit/' + questionnaire_id,
                data: new FormData(form),
                cache: false,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function(output) {
                    jQuery(form).replaceWith(output);
                    jQuery(document).trigger('dialog-open');
                }
            });
            jQuery(form).css('opacity', '0.5');
            return false;
        } else {
            return true;
        }
    },
    Test: {
        updateCheckboxValues: function() {
            jQuery('.questionnaire_edit .question.test').each(function() {
                jQuery(this)
                    .find('.options > li')
                    .each(function(index, li) {
                        jQuery(li)
                            .find('input[type=checkbox]')
                            .val(index + 1);
                    });
            });
        }
    },
    Vote: {
        validator: function () {
            if ($(this).find(".mandatory").length > 0) {
                if ($(this).find(":selected, :checked").length === 0) {
                    $(this).find(".invalidation_notice").addClass("invalid");
                    return false;
                } else {
                    $(this).find(".invalidation_notice").removeClass("invalid");
                }
            }
            return true;
        }
    },
    addQuestion: function(questiontype) {
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/questionnaire/add_question',
            data: {
                questiontype: questiontype
            },
            dataType: 'json',
            success: function(output) {
                var order = JSON.parse(jQuery("input[name=order]").val());
                order.push(output.question_id);
                jQuery("input[name=order]").val(JSON.stringify(order));
                jQuery(output.html)
                    .hide()
                    .appendTo('.questionnaire_edit .all_questions')
                    .show('fade');
            }
        });
    },
    moveQuestionUp: function () {
        let thisquestion = jQuery(this).closest(".question");
        let upper = thisquestion.prev();
        thisquestion.insertBefore(upper);
        upper.hide().fadeIn();
        thisquestion.hide().fadeIn();
    },
    moveQuestionDown: function () {
        let thisquestion = jQuery(this).closest(".question");
        let downer = thisquestion.next();
        thisquestion.insertAfter(downer);
        downer.hide().fadeIn();
        thisquestion.hide().fadeIn();
    },
    initVoteEvaluation: async function (el, data, isAjax, isMultiple) {

        const Chartist = await STUDIP.loadChunk('chartist');

        if (isAjax) {
            jQuery(document).add(".questionnaire_results").one("dialog-open", enhance);
        } else {
            jQuery(enhance);
        }

        function enhance() {
            if (isMultiple) {
                new Chartist.Bar(
                    el,
                    data,
                    { onlyInteger: true, axisY: { onlyInteger: true } }
                );
            } else {
                data.series = data.series[0];
                new Chartist.Pie(
                    el,
                    data,
                    { labelPosition: 'outside' }
                );
            }
        };
    },
    initTestEvaluation: async function (el, data, isAjax, isMultiple) {
        this.initVoteEvaluation(el, data, isAjax, isMultiple);
    },
};

export default Questionnaire;
