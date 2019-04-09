<form
    action="<?= URLHelper::getLink("dispatch.php/questionnaire/answer/".$questionnaire->getId()) ?>"
    method="post"
    enctype="multipart/form-data"
    class="questionnaire"
    <? if (Request::isAjax()) : ?>
        data-dialog
    <? endif ?>
    >
    <? if ($range_type && $range_id) : ?>
        <input type="hidden" name="range_type" value="<?= htmlReady($range_type) ?>">
        <input type="hidden" name="range_id" value="<?= htmlReady($range_id) ?>">
    <? endif ?>
    <div class="questionnaire_answer">
        <? foreach ($questionnaire->questions as $question) : ?>
            <? $template = $question->getDisplayTemplate() ?>
            <? if ($template) : ?>
                <article>
                    <?= $template->render() ?>
                </article>
            <? endif ?>
        <? endforeach ?>
    </div>

    <div class="terms">
        <? if ($questionnaire['anonymous']) : ?>
            <?= _("Die Teilnahme ist anonym.") ?>
        <? else : ?>
            <?= _("Die Teilnahme ist nicht anonym.") ?>
        <? endif ?>
        <? if ($questionnaire['editanswers']) : ?>
            <?= _("Sie können Ihre Antworten nachträglich ändern.") ?>
        <? endif ?>
        <? if ($questionnaire['stopdate']) : ?>
            <?= sprintf(_("Sie können den Fragebogen beantworten bis zum %s um %s Uhr."), date("d.m.Y", $questionnaire['stopdate']), date("H:i", $questionnaire['stopdate'])) ?>
        <? endif ?>
    </div>

    <div data-dialog-button style="text-align: center;">
        <? if ($questionnaire->isAnswerable()) : ?>
            <?= \Studip\Button::create(_("Speichern"), 'questionnaire_answer', ['onClick' => "return STUDIP.Questionnaire.beforeAnswer.call(this);"]) ?>
        <? endif ?>
        <? if ($questionnaire->resultsVisible()) : ?>
            <?= \Studip\LinkButton::create(_("Ergebnisse anzeigen"), URLHelper::getURL("dispatch.php/questionnaire/evaluate/".$questionnaire->getId()), ['data-dialog' => "1"]) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && (!$questionnaire->isRunning() || !$questionnaire->countAnswers())) : ?>
            <?= \Studip\LinkButton::create(_("Bearbeiten"), URLHelper::getURL("dispatch.php/questionnaire/edit/".$questionnaire->getId()), ['data-dialog' => "1"]) ?>
        <? endif ?>
        <? if ($questionnaire->isCopyable()) : ?>
            <?= \Studip\LinkButton::create(_("Kopieren"), URLHelper::getURL("dispatch.php/questionnaire/copy/".$questionnaire->getId()), ['data-dialog' => "1"]) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && (!$questionnaire->isRunning())) : ?>
            <?= \Studip\LinkButton::create(_("Starten"), URLHelper::getURL("dispatch.php/questionnaire/start/".$questionnaire->getId(), in_array($range_type, ['course', 'insitute']) ? ['redirect' => $range_type . "/overview"] : [])) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && $questionnaire->isRunning()) : ?>
            <?= \Studip\LinkButton::create(_("Beenden"), URLHelper::getURL("dispatch.php/questionnaire/stop/".$questionnaire->getId(), in_array($range_type, ['course', 'insitute']) ? ['redirect' => $range_type . "/overview"] : [])) ?>
        <? endif ?>
    </div>
</form>