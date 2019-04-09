<div class="questionnaire_results questionnaire_<?= $questionnaire->getId() ?>" data-questionnaire_id="<?= $questionnaire->getId() ?>">

    <? if ($questionnaire->resultsVisible()) : ?>
        <? foreach ($questionnaire->questions as $question) : ?>
            <article class="question_<?= $question->getId() ?>">
                <? $template = $question->getResultTemplate() ?>
                <?= $template ? $template->render(['anonAnswers' => $anonAnswers]) : _("Ergebnisse konnten nicht ausgewertet werden.") ?>
            </article>
        <? endforeach ?>
    <? else : ?>
        <div style="margin-top: 13px;">
            <? if ($questionnaire['resultvisibility'] === "afterending") : ?>
                <?= MessageBox::info(_("Die Ergebnisse des Fragebogens werden veröffentlich, wenn die Befragung abgeschlossen ist.")) ?>
            <? else : ?>
                <?= MessageBox::info(_("Die Ergebnisse der Befragung werden nicht über Stud.IP ausgewertet.")) ?>
           <? endif ?>
        </div>
    <? endif ?>

    <div class="terms">
        <? if ($questionnaire['anonymous']) : ?>
            <?= _("Die Teilnahme ist anonym.") ?>
        <? else : ?>
            <?= _("Die Teilnahme ist nicht anonym.") ?>
        <? endif ?>
        <? if ($questionnaire['stopdate']) : ?>
            <?= sprintf(_("Sie können den Fragebogen beantworten bis zum %s um %s Uhr."), date("d.m.Y", $questionnaire['stopdate']), date("H:i", $questionnaire['stopdate'])) ?>
        <? endif ?>
    </div>

    <script>
        STUDIP.Questionnaire.lastUpdate = Math.floor(Date.now() / 1000);
    </script>
    <div data-dialog-button style="max-height: none; opacity: 1; text-align: center;">
        <? if ($questionnaire->isAnswerable() && $questionnaire['editanswers']) : ?>
            <?= \Studip\LinkButton::create($questionnaire->isAnswered() ? _("Antwort ändern") : _("Beantworten"), URLHelper::getURL("dispatch.php/questionnaire/answer/".$questionnaire->getId(), ['range_type' => $range_type, 'range_id' => $range_id]), ['data-dialog' => "1"]) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable()) : ?>
            <?= \Studip\LinkButton::create(_("Ergebnisse herunterladen"), URLHelper::getURL("dispatch.php/questionnaire/export/".$questionnaire->getId())) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && (!$questionnaire->isRunning() || !$questionnaire->countAnswers())) : ?>
            <?= \Studip\LinkButton::create(_("Bearbeiten"), URLHelper::getURL("dispatch.php/questionnaire/edit/".$questionnaire->getId(), ['range_type' => $range_type, 'range_id' => $range_id]), ['data-dialog' => "1"]) ?>
        <? endif ?>
        <? if ($questionnaire->isCopyable()) : ?>
            <?= \Studip\LinkButton::create(_("Kopieren"), URLHelper::getURL("dispatch.php/questionnaire/copy/".$questionnaire->getId()), ['data-dialog' => "1"]) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && !$questionnaire->isRunning()) : ?>
            <?= \Studip\LinkButton::create(_("Starten"), URLHelper::getURL("dispatch.php/questionnaire/start/".$questionnaire->getId(),  in_array($range_type, ['course', 'insitute']) ? ['redirect' => $range_type . "/overview"] : [])) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && $questionnaire->isRunning()) : ?>
            <?= \Studip\LinkButton::create(_("Beenden"), URLHelper::getURL("dispatch.php/questionnaire/stop/".$questionnaire->getId(), in_array($range_type, ['course', 'insitute']) ? ['redirect' => $range_type . "/overview"] : [])) ?>
        <? endif ?>

    </div>
</div>
