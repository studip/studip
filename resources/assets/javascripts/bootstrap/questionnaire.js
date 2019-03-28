jQuery(document).on('paste', '.questionnaire_edit .options > li input', function(ui) {
    var event = ui.originalEvent;
    var text = event.clipboardData.getData('text');
    text = text.split(/[\n\t]/);
    if (text.length > 1) {
        if (text[0]) {
            this.value += text.shift().trim();
        }
        var current = jQuery(this).closest('li');
        for (var i in text) {
            if (text[i].trim()) {
                var li = jQuery(
                    jQuery(this)
                        .closest('.options')
                        .data('optiontemplate')
                );
                li.find('input:text').val(text[i].trim());
                li.insertAfter(current);
                current = li;
            }
        }
        STUDIP.Questionnaire.Test.updateCheckboxValues();
        event.preventDefault();
    }
});
jQuery(document).on('blur', '.questionnaire_edit .options > li:last-child input:text', function() {
    if (this.value) {
        jQuery(this)
            .closest('.options')
            .append(
                jQuery(this)
                    .closest('.options')
                    .data('optiontemplate')
            );
        jQuery(this)
            .closest('.options')
            .find('li:last-child input')
            .focus();
    }
    STUDIP.Questionnaire.Test.updateCheckboxValues();
});
jQuery(document).on('click', '.questionnaire_edit .options .delete', function() {
    var icon = this;
    STUDIP.Dialog.confirm(
        jQuery(this)
            .closest('.questionnaire_edit')
            .find('.delete_question')
            .text(),
        function() {
            jQuery(icon)
                .closest('li')
                .fadeOut(function() {
                    jQuery(this).remove();
                    STUDIP.Questionnaire.Test.updateCheckboxValues();
                });
        }
    );
});
jQuery(document).on('click', '.questionnaire_edit .options .add', function() {
    jQuery(this)
        .closest('.options')
        .append(
            jQuery(this)
                .closest('.options')
                .data('optiontemplate')
        );
    jQuery(this)
        .closest('.options')
        .find('li:last-child input:text')
        .focus();
    STUDIP.Questionnaire.Test.updateCheckboxValues();
});

/*
 * This fixes the tab problem in chartist see:
 * https://github.com/gionkunz/chartist-js/issues/119
 */
jQuery(document).on('click', 'article.studip .toggle', function() {
    jQuery(this)
        .find('.ct-chart')
        .each(function(i, e) {
            e.__chartist__.update();
        });
});
