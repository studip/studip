<? $class = get_class($question) ?>
<fieldset data-questiontype="<?= htmlReady($class) ?>"
          class="question <?= htmlReady(mb_strtolower($class)) ?>">
    <legend>
        <div style="float: right; padding-top: 3px; padding-right: 5px;">
            <a href="#" class="move_up" title="<?= _("Frage nach oben verschieben") ?>"
               onClick="STUDIP.Questionnaire.moveQuestionUp.call(this); return false;">
                <?= Icon::create("arr_1up", "clickable")->asImg("20px", ['class' => "text-bottom"]) ?>
            </a>
            <a href="#" class="move_down" title="<?= _("Frage nach unten verschieben") ?>"
               onClick="STUDIP.Questionnaire.moveQuestionDown.call(this); return false;">
                <?= Icon::create("arr_1down", "clickable")->asImg("20px", ['class' => "text-bottom"]) ?>
            </a>
            <a href="#" onClick="var that = this; STUDIP.Dialog.confirm('<?= _("Wirklich löschen?") ?>', function () { jQuery(that).closest('fieldset').remove(); }); return false;"
               title="<?= sprintf(_("%s löschen"), htmlReady($class::getName())) ?>">
                <?= Icon::create("trash", "clickable")->asImg("20px", ['class' => "text-bottom"]) ?>
            </a>
        </div>
        <div>
            <?= $class::getIcon()->asImg("20px", ['class' => "text-bottom"]) ?>
            <?= htmlReady($class::getName()) ?>
        </div>

    </legend>
    <input type="hidden" name="neworder[]" value="<?= htmlReady($question->getId()) ?>">
    <input type="hidden" name="question_types[<?= htmlReady($question->getId()) ?>]" value="<?= htmlReady(get_class($question)) ?>">
    <?= $question->getEditingTemplate()->render() ?>
</fieldset>
