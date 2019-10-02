<form action="<?= URLHelper::getLink("dispatch.php/questionnaire/edit/".(!$questionnaire->isNew() ? $questionnaire->getId() : "")) ?>"
      method="post" enctype="multipart/form-data"
      class="questionnaire_edit default"
      <?= Request::isAjax() ? "data-dialog" : "" ?>>
    <input type="hidden" name="order" value="<?= htmlReady(json_encode($order)) ?>">
    <? if (Request::get("range_id")) : ?>
        <input type="hidden" name="range_id" value="<?= htmlReady(Request::get("range_id")) ?>">
        <input type="hidden" name="range_type" value="<?= htmlReady(Request::get("range_type", "static")) ?>">
    <? endif ?>
    <fieldset>
        <legend><?= _("Fragebogen") ?></legend>
        <label>
            <?= _("Titel des Fragebogens") ?>
            <input type="text" name="questionnaire[title]" value="<?= htmlReady($questionnaire['title']) ?>" class="size-l" required>
        </label>
    </fieldset>

    <? foreach ($questionnaire->questions as $index => $question) : ?>
        <?= $this->render_partial("questionnaire/_question.php", compact("question")) ?>
    <? endforeach ?>

    <div style="text-align: right;" class="add_questions">
        <? foreach (get_declared_classes() as $class) :
            if (in_array('QuestionType', class_implements($class))) : ?>
                <a href="" onClick="STUDIP.Questionnaire.addQuestion('<?= htmlReady($class) ?>'); return false;">
                    <?= $class::getIcon(true, true)->asimg("40px") ?>
                    <?= htmlReady($class::getName()) ?>
                </a>
            <? endif;
        endforeach ?>
    </div>

    <fieldset class="questionnaire_metadata">

        <label>
            <?= _("Startzeitpunkt (leer lassen für manuellen Start)") ?>
            <input type="text" name="questionnaire[startdate]" value="<?= $questionnaire['startdate'] ? date("d.m.Y H:i", $questionnaire['startdate']) : ($questionnaire->isNew() ? _("sofort") : "") ?>" data-datetime-picker>
        </label>

        <label>
            <?= _("Endzeitpunkt (leer lassen für manuelles Ende)") ?>
            <input type="text" name="questionnaire[stopdate]" value="<?= $questionnaire['stopdate'] ? date("d.m.Y H:i", $questionnaire['stopdate']) : "" ?>" data-datetime-picker>
        </label>

        <label>
            <input type="checkbox" name="questionnaire[copyable]" value="1"<?= $questionnaire['copyable'] ? " checked" : "" ?>>
            <?= _("Frageboben zum Kopieren freigeben") ?>
        </label>

        <label>
            <input type="checkbox" name="questionnaire[anonymous]" onChange="jQuery('#questionnaire_editanswers').toggle(!this.checked);" value="1"<?= $questionnaire['anonymous'] ? " checked" : "" ?>>
            <?= _("Anonym teilnehmen") ?>
        </label>

        <label id="questionnaire_editanswers" <?= $questionnaire['anonymous'] ? 'style="display: none"' : '' ?>>
            <input type="checkbox" name="questionnaire[editanswers]" value="1"<?= $questionnaire['editanswers'] || $questionnaire->isNew() ? " checked" : "" ?>>
            <?= _("Teilnehmende dürfen ihre Antworten revidieren") ?>
        </label>

        <label>
            <?= _("Ergebnisse an Teilnehmende") ?>
            <select name="questionnaire[resultvisibility]">
                <option value="always"<?= $questionnaire['resultvisibility'] === "always" ? " selected" : "" ?>><?= _("Immer.") ?></option>
                <option value="afterending"<?= $questionnaire['resultvisibility'] === "afterending" ? " selected" : "" ?>><?= _("Nach Ende der Befragung.") ?></option>
                <option value="never"<?= $questionnaire['resultvisibility'] === "never" ? " selected" : "" ?>><?= _("Niemals.") ?></option>
            </select>
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), 'questionnaire_store') ?>
    </footer>
</form>
