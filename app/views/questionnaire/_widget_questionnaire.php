<article class="studip toggle <?= ContentBoxHelper::classes($questionnaire->id, $is_new) ?> widget_questionnaire_<?= $questionnaire->getId() ?>"  data-questionnaire_id="<?= htmlReady($questionnaire->getId()) ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::switchhref($questionnaire->id, ['contentbox_type' => 'vote']) ?>">
                <?= htmlReady($questionnaire->title) ?>
            </a>
        </h1>
        <nav>
            <a href="<?= $questionnaire->user_id ? URLHelper::getLink('dispatch.php/profile', ['username' => get_username($questionnaire->user_id)]) : '' ?>">
                <?= $questionnaire->user_id ? htmlReady(get_fullname($questionnaire->user_id)) : '' ?>
            </a>
            <span>
                <?= strftime("%d.%m.%Y", $questionnaire->mkdate) ?>
            </span>
            <span title="<?= _("Anzahl der Antworten") ?>">
                <?= $questionnaire->countAnswers() ?>
            </span>
            <span title="<?= _("QR-Code zu diesem Fragebogen anzeigen") ?>">
                <? $oldbase = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']) ?>
                <a href="<?= URLHelper::getLink("dispatch.php/questionnaire/answer/".$questionnaire->getId()) ?>"
                   class="questionnaire-qr"
                    data-qr-code>
                    <? URLHelper::setBaseURL($oldbase) ?>
                    <?= Icon::create("code-qr", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                </a>
            </span>
        </nav>
    </header>
    <section>
        <? if ($questionnaire->isAnswered() || $questionnaire->isStopped() || !$questionnaire->isAnswerable()) : ?>
            <?= $this->render_partial('questionnaire/evaluate.php', ['questionnaire' => $questionnaire, 'range_type' => $range_type, 'range_id' => $range_id]); ?>
        <? else : ?>
            <?= $this->render_partial('questionnaire/answer.php', ['questionnaire' => $questionnaire, 'range_type' => $range_type, 'range_id' => $range_id]); ?>
        <? endif ?>
    </section>
</article>