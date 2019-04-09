<? $allowed_to_add = ($range_id === $GLOBALS['user']->id && $range_type === "user") || ($range_id === "start" && $GLOBALS['perm']->have_perm("root")) || ($range_type === "course" && $GLOBALS['perm']->have_studip_perm("tutor", $range_id)) || ($range_type === "institute" && $GLOBALS['perm']->have_studip_perm("tutor", $range_id)) ?>
<article class="studip questionnaire_widget" id="questionnaire_area">
    <header>
        <h1>
            <?= Icon::create("evaluation", "info")->asimg("16px", ['class' => "text-bottom"]) ?>
            <?= _('Fragebögen') ?>
        </h1>
        <nav>
            <? if ($allowed_to_add) : ?>
                <a href="<?= URLHelper::getLink("dispatch.php/questionnaire/edit", ['range_id' => $range_id, 'range_type' => $range_type]) ?>" data-dialog title="<?= _('Fragebogen hinzufügen') ?>">
                    <?= Icon::create("add", "clickable")->asimg("16px", ['class' => "text-bottom"]) ?>
                </a>
                <a href="<?= URLHelper::getLink("dispatch.php/questionnaire/" . ($range_type == 'course' || $range_type == 'institute' ? 'course' : ''). "overview") ?>" title="<?= _('Fragebögen verwalten') ?>">
                    <?= Icon::create("edit", "clickable")->asimg("16px", ['class' => "text-bottom"]) ?>
                </a>
            <? endif ?>
        </nav>
    </header>

    <? if ($questionnaire_data): ?>
        <? foreach ($questionnaire_data as $questionnaire): ?>
            <?= $this->render_partial("questionnaire/_widget_questionnaire", ['questionnaire' => Questionnaire::buildExisting($questionnaire), 'range_type' => $range_type, 'range_id' => $range_id]) ?>
        <? endforeach; ?>
    <? elseif (!$suppress_empty_output): ?>
        <section class="noquestionnaires">
            <?= _('Es sind keine Fragebögen vorhanden.') ?>
            <? if ($allowed_to_add) : ?>
                <?= _("Um neue Fragebögen zu erstellen, klicken Sie rechts auf das Plus.") ?>
            <? endif ?>
        </section>
    <? endif; ?>
        <footer>
            <? if (Request::get('questionnaire_showall')): ?>
                <a href="<?= URLHelper::getLink('#questionnaire_area', ['questionnaire_showall' => 0]) ?>"><?= _('Abgelaufene Fragebögen ausblenden') ?></a>
            <? else: ?>
                <a href="<?= URLHelper::getLink('#questionnaire_area', ['questionnaire_showall' => 1]) ?>"><?= _('Abgelaufene Fragebögen einblenden') ?></a>
            <? endif; ?>
        </footer>
</article>
